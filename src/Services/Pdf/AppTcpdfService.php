<?php
declare(strict_types=1);

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under the GNU Affero General Public License version 3
 * For full copyright and license information, please see LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       https://opensource.org/licenses/AGPL-3.0
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
namespace App\Services\Pdf;

use App\Controller\Component\StringComponent;
use App\Services\OutputFilter\OutputFilterService;
use Cake\Core\Configure;
use Cake\View\Helper\TextHelper;
use Com\Tecnick\Pdf\Tcpdf;
use Com\Tecnick\Pdf\TextFitMode;
use Com\Tecnick\Pdf\TextHAlign;
use Com\Tecnick\Pdf\TextVAlign;
use Com\Tecnick\Unicode\TextDirection;

abstract class AppTcpdfService extends Tcpdf
{

    public const string THIN_TABLE_CELL_BORDER_STYLE = '0.1mm solid #000';

    public const string FONT_FAMILY = 'freesans';

    // line height relative to the font size (as in the former tcpdf library)
    protected const float CELL_HEIGHT_RATIO = 1.25;

    protected const float PAGE_BREAK_MARGIN = 20.0;

    protected const float DEFAULT_MARGIN = 10.0;

    public string $table = '';

    public bool $replaceEuroSign = true;

    public string $logoPath = ROOT . DS . 'webroot' . DS . 'files' . DS . 'images' . DS . 'logo-pdf.jpg';

    public string $infoTextForFooter = '';

    public TextHelper $textHelper;

    protected string $orientation;

    protected float $leftMargin = self::DEFAULT_MARGIN;

    protected float $rightMargin = self::DEFAULT_MARGIN;

    protected float $topMargin = self::DEFAULT_MARGIN;

    protected float $fontSize = 12.0;

    protected float $cursorY = 0.0;

    protected bool $fontInitialized = false;

    protected bool $applyHalfLeading = false;

    protected bool $pagesDecorated = false;

    /**
     * @var array<int, string>
     */
    protected array $footerTexts = [];

    /**
     * @var array<int, float>
     */
    protected array $pageLeftMargins = [];

    private string $htmlLog = '';

    public function __construct(string $orientation = 'P')
    {
        $this->orientation = $orientation;
        parent::__construct(
            unit: 'mm',
            isunicode: true,
            subsetfont: true,
            compress: true,
            fileOptions: [
                'allowedPaths' => array_merge($this->defaultFileAllowedPaths(), [ROOT . DS . 'webroot', ROOT . DS . 'files_private']),
            ],
        );
        mb_internal_encoding('UTF-8');
        $this->setCreator(Configure::read('appDb.FCS_APP_NAME'));
        $this->setAuthor(Configure::read('appDb.FCS_APP_NAME'));
    }

    public function setTextHelper(TextHelper $textHelper): void
    {
        $this->textHelper = $textHelper;
    }

    public function setLeftMargin(float $margin): void
    {
        $this->leftMargin = $margin;
    }

    public function setRightMargin(float $margin): void
    {
        $this->rightMargin = $margin;
    }

    public function setTopMargin(float $margin): void
    {
        $this->topMargin = $margin;
    }

    public function setFontSize(float $size): void
    {
        $this->fontSize = $size;
        if ($this->fontInitialized) {
            $font = $this->font->insert($this->pon, self::FONT_FAMILY, '', $size);
            $this->page->addContent($font['out']);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function addPage(array $data = []): array
    {
        if ($data === []) {
            $data = [
                'format' => 'A4',
                'orientation' => $this->orientation,
                'margin' => [
                    'PL' => $this->leftMargin,
                    'PR' => $this->rightMargin,
                    'CT' => $this->topMargin,
                    'CB' => self::PAGE_BREAK_MARGIN,
                ],
            ];
        }
        $page = parent::addPage($data);
        if (!$this->fontInitialized) {
            $font = $this->font->insert($this->pon, self::FONT_FAMILY, '', $this->fontSize);
            $this->page->addContent($font['out']);
            $this->fontInitialized = true;
        }
        $this->cursorY = $this->topMargin;
        return $page;
    }

    protected function setPageContext(int $pid = -1): void
    {
        if ($pid < 0) {
            $pid = (int) $this->page->getPageId();
        }
        if ($pid > 0 && !isset($this->footerTexts[$pid - 1])) {
            $this->footerTexts[$pid - 1] = $this->infoTextForFooter;
        }
        $this->pageLeftMargins[$pid] = $this->leftMargin;
        parent::setPageContext($pid);
    }

    public function ln(float $height): void
    {
        $this->cursorY += $height;
    }

    public function writeHTML(string $html): void
    {
        $html = StringComponent::cleanForPdfGeneration($html);

        if (Configure::check('app.outputStringReplacements')) {
            $html = OutputFilterService::replace($html, Configure::read('app.outputStringReplacements'));
        }

        if ($this->replaceEuroSign) {
            $html = preg_replace('/€/', '&euro;', $html);
        }
        $this->htmlLog .= $html;

        $page = $this->page->getPage();
        $width = $page['width'] - $this->leftMargin - $this->rightMargin;
        $this->applyHalfLeading = true;
        try {
            $this->addHTMLCell($this->getHtmlDefaultStyles() . $this->prepareHtmlForRendering($html), $this->leftMargin, $this->cursorY, $width);
        } finally {
            $this->applyHalfLeading = false;
        }
        $end = $this->getLastHTMLCellEnd();
        $this->setCurrentPage($end['page']);
        $this->cursorY = $end['y'] - $this->getTrailingBlockMargin($html) + $this->getTrailingTableGap($html);
    }

    /**
     * the former tcpdf library added the height of its last rendered line after a table (values measured)
     */
    protected function getTrailingTableGap(string $html): float
    {
        if (!preg_match('/<\/table>\s*$/i', $html)) {
            return 0.0;
        }
        $lastRowPos = strripos($html, '<tr');
        $tablePos = strripos(substr($html, 0, $lastRowPos === false ? strlen($html) : $lastRowPos), '<table');
        $cellPadding = 0.0;
        if ($tablePos !== false && preg_match('/^<table[^>]*cellpadding="([0-9.]+)"/i', substr($html, $tablePos), $matches)) {
            $cellPadding = (float) $matches[1];
        }
        $hasCellPadding = $cellPadding > 0;
        $lineHeight = $this->fontSize * self::CELL_HEIGHT_RATIO;
        $gap = $hasCellPadding ? 14.5 : $lineHeight;
        if ($lastRowPos !== false) {
            $lastRow = substr($html, $lastRowPos);
            if ($cellPadding > 1 && preg_match_all('/<h([1-6])[\s>]/i', $lastRow, $matches)) {
                $headingFontSize = $this->fontSize + (4 - (int) end($matches[1])) * 2;
                $gap += $headingFontSize * self::CELL_HEIGHT_RATIO - 3;
            }
            if (preg_match('/<br\s*\/?>(\s*<\/(?!td|th)[a-z0-9]+>)*\s*<\/t[dh]>/i', $lastRow)) {
                $gap += $lineHeight;
            }
        }
        return $this->toUnit($gap);
    }

    /**
     * the former tcpdf library rendered table rows without content with the height of one line
     */
    protected function prepareHtmlForRendering(string $html): string
    {
        return (string) preg_replace_callback('/<tr(?:\s[^>]*)?>(?:(?!<tr|<table|<\/tr>).)*<\/tr>/is', function (array $matches): string {
            if (trim(strip_tags($matches[0], '<img>')) !== '') {
                return $matches[0];
            }
            return (string) preg_replace('/(<t[dh](?:\s[^>]*)?>)(<\/t[dh]>)/', '$1&nbsp;$2', $matches[0]);
        }, $html);
    }

    /**
     * the former tcpdf library did not add the bottom margin of the last block element
     */
    protected function getTrailingBlockMargin(string $html): float
    {
        if (!preg_match('/<\/(p|h[1-6])>\s*$/i', $html, $matches)) {
            return 0.0;
        }
        $tag = strtolower($matches[1]);
        if ($tag === 'p') {
            return $this->toUnit($this->fontSize * self::CELL_HEIGHT_RATIO);
        }
        $headingFontSize = $this->fontSize + (4 - (int) substr($tag, 1)) * 2;
        return $this->toUnit($headingFontSize * self::CELL_HEIGHT_RATIO * 0.6);
    }

    /**
     * emulates the vertical spacing and line height of the former tcpdf library
     */
    protected function getHtmlDefaultStyles(): string
    {
        $lineHeight = $this->fontSize * self::CELL_HEIGHT_RATIO;
        $css = '*,p,h1,h2,h3,h4,h5,h6,div,span,b,i,u,strong,em,a,td,th,tr,table,li,ul,ol{line-height:125%;orphans:1;widows:1;}';
        $css .= 'th{text-align:left;}';
        $css .= 'a{color:#0000ff;text-decoration:underline;}';
        $css .= 'td > *:last-child,th > *:last-child{margin-bottom:0;}';
        // the former tcpdf library used an additional point of right padding in table cells
        $alignedCells = [];
        foreach (['td', 'th'] as $cell) {
            foreach (['right', 'center'] as $align) {
                $alignedCells[] = 'table[cellpadding="1"] ' . $cell . '[align="' . $align . '"]';
            }
        }
        $css .= implode(',', $alignedCells) . '{padding-right:2pt;}';
        $css .= 'p{margin-top:' . $lineHeight . 'pt;margin-bottom:' . $lineHeight . 'pt;}';
        foreach ([1, 2, 3, 4, 5, 6] as $level) {
            $headingLineHeight = ($this->fontSize + (4 - $level) * 2) * self::CELL_HEIGHT_RATIO;
            $css .= 'h' . $level . '{margin-top:' . ($headingLineHeight * 0.6) . 'pt;margin-bottom:' . ($headingLineHeight * 0.6) . 'pt;}';
        }
        return '<style>' . $css . '</style>';
    }

    public function getHtml(): string
    {
        return $this->htmlLog;
    }

    public function renderTable(): void
    {
        $this->table .= '</table>';

        $this->writeHTML($this->table);

        // reset table
        $this->table = '';
    }

    public function getThinTableCellStyleAttribute(string $style = ''): string
    {
        if ($style != '' && !str_ends_with($style, ';')) {
            $style .= ';';
        }

        return ' style="' . $style . 'border:' . self::THIN_TABLE_CELL_BORDER_STYLE . ';"';
    }

    /**
     * text is shifted by the half leading to get the same baseline as the former tcpdf library
     */
    public function getTextCell(
        string $txt,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        float $offset = 0,
        float $linespace = 0,
        string|TextVAlign $valign = 'C',
        string|TextHAlign $halign = 'C',
        ?array $cell = null,
        array $styles = [],
        float $strokewidth = 0,
        float $wordspacing = 0,
        float $leading = 0,
        float $rise = 0,
        bool $jlast = true,
        bool $fill = true,
        bool $stroke = false,
        bool $underline = false,
        bool $linethrough = false,
        bool $overline = false,
        bool $clip = false,
        bool $drawcell = true,
        string|TextDirection $forcedir = '',
        ?array $shadow = null,
        string|TextFitMode $fit = '',
    ): string {
        $shift = 0.0;
        if ($this->applyHalfLeading) {
            $font = $this->font->getCurrentFont();
            $shift = $this->toUnit(($font['size'] * self::CELL_HEIGHT_RATIO - $font['height']) / 2);
            $posy += $shift;
        }
        $lastKeys = [
            'bbox' => array_key_last($this->bbox),
            'textbbox' => array_key_last($this->textbbox),
            'cellbbox' => array_key_last($this->cellbbox),
        ];
        $out = parent::getTextCell($txt, $posx, $posy, $width, $height, $offset, $linespace, $valign, $halign, $cell, $styles, $strokewidth, $wordspacing, $leading, $rise, $jlast, $fill, $stroke, $underline, $linethrough, $overline, $clip, $drawcell, $forcedir, $shadow, $fit);
        if ($shift != 0.0) {
            // layout calculations must not be affected by the shift
            foreach ($lastKeys as $property => $lastKey) {
                $key = array_key_last($this->$property);
                if ($key !== null && $key !== $lastKey) {
                    $this->$property[$key]['y'] -= $shift;
                }
            }
        }
        return $out;
    }

    /**
     * former tcpdf library treated px as pt
     * @param array{'font': array{'rootsize': float, 'size': float, 'xheight': float, 'zerowidth': float}, 'page': array{'height': float, 'width': float}, 'parent': float, 'viewport': array{'height': float, 'width': float}} $ref
     */
    protected function getUnitValuePoints(string|float|int $val, array $ref = self::REFUNITVAL, string $defunit = 'px'): float
    {
        if (is_string($val) && preg_match('/^\s*([0-9.\-+]+)\s*px\s*$/', $val, $matches)) {
            return parent::getUnitValuePoints($matches[1], $ref, 'pt');
        }
        if (is_numeric($val) && $defunit === 'px') {
            $defunit = 'pt';
        }
        return parent::getUnitValuePoints($val, $ref, $defunit);
    }

    protected function getPageHeader(int $pid): string
    {
        return '';
    }

    protected function getPageFooter(int $pid, int $pageCount): string
    {
        return '';
    }

    protected function drawLine(float $posy): string
    {
        $page = $this->page->getPage();
        return $this->graph->getLine(0, $posy, $page['width'], $posy, [
            'lineWidth' => 0.57 / 72 * 25.4,
            'lineColor' => 'black',
        ]);
    }

    protected function getLogo(float $posx, float $posy, float $width): string
    {
        $imageId = $this->image->add('@' . file_get_contents($this->logoPath));
        $size = getimagesize($this->logoPath);
        $height = $size === false ? 0 : $width * $size[1] / $size[0];
        $page = $this->page->getPage();
        return $this->image->getSetImage($imageId, $posx, $posy, $width, $height, $page['height']);
    }

    /**
     * @param callable(): string $callback
     */
    protected function withFont(float $size, callable $callback): string
    {
        $font = $this->font->insert($this->pon, self::FONT_FAMILY, '', $size);
        $out = $font['out'] . $callback();
        $this->font->popLastFont();
        return $out;
    }

    /**
     * renders html lines (separated by br) right aligned like the former tcpdf MultiCell
     */
    protected function getHeaderRightText(string $html, float $rightEdge): string
    {
        $lines = explode('<br />', $html);
        if ($lines[0] === '') {
            array_shift($lines);
        }
        return $this->withFont(10, function () use ($lines, $rightEdge): string {
            $font = $this->font->getCurrentFont();
            $lineHeight = $this->toUnit($font['size'] * self::CELL_HEIGHT_RATIO);
            $halfLeading = $this->toUnit(($font['size'] * self::CELL_HEIGHT_RATIO - $font['height']) / 2);
            $out = '';
            foreach ($lines as $i => $line) {
                $text = trim(html_entity_decode(strip_tags($line), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($text === '') {
                    continue;
                }
                $out .= parent::getTextCell($text, 0, 4 + $i * $lineHeight + $halfLeading, $rightEdge, 0, 0, 0, 'T', 'R', self::ZEROCELL);
            }
            return $out;
        });
    }

    /**
     * draws a single line of text with the baseline positioned like the former tcpdf library (valign middle)
     */
    protected function getTcpdfCellText(string $text, float $posx, float $posy, float $height, string $align, float $width): string
    {
        $font = $this->font->getCurrentFont();
        $ascent = $this->toUnit($font['ascent']);
        $descent = $this->toUnit(abs($font['descent']));
        $top = $posy + ($height - $ascent - $descent) / 2;
        return parent::getTextCell($text, $posx, $top, $width, 0, 0, 0, 'T', $align, self::ZEROCELL);
    }

    protected function decoratePages(): void
    {
        if ($this->pagesDecorated) {
            return;
        }
        $this->pagesDecorated = true;
        $pages = $this->page->getPages();
        $pageCount = count($pages);
        $currentPageId = (int) $this->page->getPageId();
        $lastPageId = (int) array_key_last($pages);
        if (!isset($this->footerTexts[$lastPageId])) {
            $this->footerTexts[$lastPageId] = $this->infoTextForFooter;
        }
        foreach (array_keys($pages) as $pid) {
            $pid = (int) $pid;
            $this->setCurrentPage($pid);
            $out = $this->graph->getStartTransform();
            $out .= $this->getPageHeader($pid);
            $out .= $this->getPageFooter($pid, $pageCount);
            $out .= $this->graph->getStopTransform();
            $this->page->addContent($out, $pid);
        }
        $this->setCurrentPage($currentPageId);
    }

    public function getOutPDFString(): string
    {
        $this->decoratePages();
        return parent::getOutPDFString();
    }

}
