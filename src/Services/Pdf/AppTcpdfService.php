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

abstract class AppTcpdfService extends Tcpdf
{

    public const string THIN_TABLE_CELL_BORDER_STYLE = '0.1mm solid #000';

    public const string FONT_FAMILY = 'freesans'; // freesans supports cyrillic characters

    protected const float PAGE_BREAK_MARGIN = 20.0;

    protected const float DEFAULT_MARGIN = 10.0;

    final protected const string HTML_DEFAULT_STYLES = '<style>body{line-height:125%;}p{margin-bottom:0;}td,th{vertical-align:middle;}td,th,td *,th *{line-height:normal;}th{text-align:left;}a{color:#0000ff;text-decoration:underline;}</style>';

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

    /**
     * @var array<int, float>
     */
    protected array $pageLeftMargins = [];

    private string $htmlLog = '';

    public function __construct(string $orientation = 'P')
    {
        $this->orientation = $orientation;
        parent::__construct(
            fileOptions: [
                'allowedPaths' => array_merge($this->defaultFileAllowedPaths(), [ROOT . DS . 'webroot', ROOT . DS . 'files_private']),
            ],
        );
        $this->font->insert($this->pon, self::FONT_FAMILY, '', $this->fontSize);
        $this->setHtmlVSpace(['table' => [1 => ['n' => 1]]]);
        mb_internal_encoding('UTF-8');
        $this->setCreator(Configure::read('appDb.FCS_APP_NAME'));
        $this->setAuthor(Configure::read('appDb.FCS_APP_NAME'));
        $this->enableDefaultPageContent();
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
        $font = $this->font->insert($this->pon, self::FONT_FAMILY, '', $size);
        if ($this->page->getPageId() >= 0) {
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
        $this->cursorY = $this->topMargin;
        return $page;
    }

    protected function setPageContext(int $pid = -1): void
    {
        if ($pid < 0) {
            $pid = (int) $this->page->getPageId();
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
        $this->addHTMLCell(self::HTML_DEFAULT_STYLES . $html, $this->leftMargin, $this->cursorY, $width);
        $end = $this->getLastHTMLCellEnd();
        $this->setCurrentPage($end['page']);
        $this->cursorY = $end['y'];
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

    protected function getUnitValuePoints(string|float|int $val, array $ref = self::REFUNITVAL, string $defunit = 'px'): float
    {
        return parent::getUnitValuePoints(
            is_string($val) ? str_replace('px', 'pt', $val) : $val,
            $ref,
            $defunit === 'px' ? 'pt' : $defunit,
        );
    }

    protected function getPageHeader(int $pid): string
    {
        return '';
    }

    protected function getPageFooter(int $pid): string
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
    protected function withFont(float $size, callable $callback, string $family = self::FONT_FAMILY): string
    {
        $font = $this->font->insert($this->pon, $family, '', $size);
        $out = $font['out'] . $callback();
        $this->font->popLastFont();
        return $out;
    }

    protected function getHeaderRightText(string $html, float $rightEdge): string
    {
        $text = html_entity_decode(strip_tags(str_replace('<br />', "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_starts_with($text, "\n")) {
            $text = substr($text, 1);
        }
        return $this->withFont(10, fn(): string => $this->getTextCell($text, 0, 4, $rightEdge, halign: 'R', cell: self::ZEROCELL,));
    }

    public function defaultPageContent(int $pid = -1): string
    {
        $currentPageId = (int) $this->page->getPageId();
        if ($pid < 0) {
            $pid = $currentPageId;
        }
        $this->setCurrentPage($pid);
        try {
            $out = $this->graph->getStartTransform();
            $out .= $this->getPageHeader($pid);
            $out .= $this->getPageFooter($pid);
            $out .= $this->graph->getStopTransform();
            return $out;
        } finally {
            $this->setCurrentPage($currentPageId);
        }
    }

}
