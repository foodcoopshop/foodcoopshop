<?php
declare(strict_types=1);

use Cake\I18n\I18n;
use Cake\Core\Configure;
use App\Model\Entity\Block;
use App\Model\Table\BlocksTable;
use Cake\ORM\TableRegistry;
use Migrations\BaseMigration;

class AddHomeBlocks extends BaseMigration
{
    public function up(): void
    {
        if (I18n::getLocale() !== 'de_DE') {
            return;
        }

        if ($this->hasExistingBlocks()) {
            return;
        }

        $blockRows = [
            [
                'image' => '1',
                'image_position' => Block::IMAGE_POSITION_LEFT,
                'heading' => "FoodCoop - Gemeinsam schmeckt's besser!",
                'content' => '<p>Eine FoodCoop ist eine Gemeinschaft von Menschen, die gemeinsam Lebensmittel direkt von regionalen Produzent:innen bezieht. Statt anonym im Supermarkt einzukaufen, organisieren wir uns selbst hochwertige Lebensmittel aus unserer Region - fair, transparent und nachhaltig.</p><p><strong>Das bedeutet:</strong></p><ul><li>frische Lebensmittel von lokalen Erzeuger:innen</li><li>direkter Kontakt zu den Bäuer:innen</li><li>sorfältig ausgewählte Betriebe mit Fokus auf biologische Landwirtschaft</li><li>weniger Verpackung und Transport</li><li>faire Preise für alle Beteiligten</li><li>Gemeinschaft statt Konsum allein</li></ul>',
                'primary_label' => null,
                'primary_href' => null,
                'secondary_label' => null,
                'secondary_href' => null,
                'position' => 0,
                'active' => 1,
                'seed_image' => 'import-home-block-1.jpg',
            ],
            [
                'image' => null,
                'image_position' => Block::IMAGE_POSITION_LEFT,
                'heading' => "So funktioniert's!",
                'content' => '<p>Als Mitglied kannst du jede Woche bis Dienstag Mitternacht bequem online deine Lebensmittel bestellen und sie freitags gesammelt im Abholraum abholen. Du hilfst bei kleinen organisatorischen Aufgaben mit - zum Beispiel bei der Ausgabe, Planung oder Kommunikation. Dadurch bleibt die FoodCoop unabhängig und gemeinschaftlich organisiert. Du musst kein Profi sein: Jede Person bringt ein, was möglich ist.<br></p>',
                'primary_label' => 'Jetzt anmelden!',
                'primary_href' => Configure::read('App.fullBaseUrl') . Configure::read('app.slugHelper')->getLogin(),
                'secondary_label' => null,
                'secondary_href' => null,
                'position' => 1,
                'active' => 1,
                'seed_image' => null,
            ],
            [
                'image' => '1',
                'image_position' => Block::IMAGE_POSITION_RIGHT,
                'heading' => 'Das sind wir',
                'content' => '<p>Wir sind eine Gruppe von Menschen aus der Region, die gutes Essen, faire Landwirtschaft und gemeinschaftliches Handeln schätzen. Als Foodcoop bestellen wir unsere Lebensmittel direkt bei Produzent:innen, die wir kennen und denen wir vertrauen. So entstehen kurze Wege, transparente Beziehungen und hochwertige Produkte. 💖</p><p>Uns verbindet die Freude an gutem Essen und die Überzeugung, dass ein bewusster Umgang mit Lebensmitteln einen Unterschied macht. Gemeinsam organisieren wir Einkauf, Verteilung und das Miteinander - unkompliziert, fair und mit viel Herz.</p><p>Neue Gesichter sind bei uns jederzeit willkommen! - Wir freuen uns auf dich!</p>',
                'primary_label' => null,
                'primary_href' => null,
                'secondary_label' => null,
                'secondary_href' => null,
                'position' => 2,
                'active' => 1,
                'seed_image' => 'import-home-block-3.jpg',
            ],
            [
                'image' => '1',
                'image_position' => Block::IMAGE_POSITION_LEFT,
                'heading' => 'Mehr als Einkaufen',
                'content' => '<p>Eine FoodCoop verbindet Menschen, die bewusster leben und ihr Ernährungssystem aktiv mitgestalten wollen. Es entsteht ein Ort für Gemeinschaft und Wissen:</p><p><em>Workshops und Filmabende</em><em><br>Rezeptaustausch mit saisonalen Lebensmitteln</em><em><br>gemeinsames Lernen über Ernährung und Landwirtschaft</em><em><br>Begegnungen mit unseren Bäuer:innen und Exkursionen</em><em><br>Austausch über nachhaltiges Leben und Konsum</em><em><br>Saisonalität wird wieder erlebbar - nicht als Verzicht, sondern als neue Wertschätzung für Lebensmittel, Natur und regionale Kreisläufe.</em></p><p>Wenn du Lebensmittel nicht nur konsumieren, sondern verstehen und mitgestalten möchtest, bist du bei uns genau richtig.</p><p><br>Komm vorbei, lerne uns kennen und werde Teil der FoodCoop!</p>',
                'primary_label' => null,
                'primary_href' => null,
                'secondary_label' => null,
                'secondary_href' => null,
                'position' => 3,
                'active' => 1,
                'seed_image' => 'import-home-block-4.jpg',
            ],
            [
                'image' => '1',
                'image_position' => Block::IMAGE_POSITION_LEFT,
                'heading' => '',
                'content' => '<p>Die Neugestaltung der Landingpage wurde unterstützt vom Projekt <em>Appetit auf Gutes</em> - eine Kooperation von BIO AUSTRIA OÖ, Klimabündnis OÖ und dem Umweltressort des Landes Oberösterreichs.</p><p><em>Appetit auf Gutes </em>unterstützt seit 2014 SoLaWis und FoodCoops in ganz Oberösterreich.<br><br>Weitere Infos unter <a href="https://www.bio-austria.at/aaz" target="_blank" rel="noreferrer noopener">https://www.bio-austria.at/aaz</a></p>',
                'primary_label' => null,
                'primary_href' => null,
                'secondary_label' => null,
                'secondary_href' => null,
                'position' => 4,
                'active' => 1,
                'seed_image' => 'import-home-block-5.jpg',
            ],
        ];

        /** @var BlocksTable $blocksTable */
        $blocksTable = TableRegistry::getTableLocator()->get('Blocks');

        foreach ($blockRows as $blockRow) {
            $seedImage = $blockRow['seed_image'];
            unset($blockRow['seed_image']);

            $blockEntity = $blocksTable->newEntity($blockRow);
            $savedBlock = $blocksTable->saveOrFail($blockEntity);

            if ($seedImage === null) {
                continue;
            }

            $this->copySeedImageToBlockFolder((int)$savedBlock->id, $seedImage);
        }
    }

    private function hasExistingBlocks(): bool
    {
        /** @var BlocksTable $blocksTable */
        $blocksTable = TableRegistry::getTableLocator()->get('Blocks');
        return $blocksTable->find()->count() > 0;
    }

    private function copySeedImageToBlockFolder(int $blockId, string $seedFilename): void
    {
        $sourcePath = ROOT . DS . 'tmp' . DS . 'seeds' . DS . 'images' . DS . $seedFilename;
        if (!file_exists($sourcePath)) {
            throw new RuntimeException('Missing seed image: ' . $sourcePath);
        }

        $targetDir = WWW_ROOT . 'files' . DS . 'images' . DS . 'blocks';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Could not create block image directory: ' . $targetDir);
        }

        $extension = strtolower(pathinfo($seedFilename, PATHINFO_EXTENSION));
        $targetPath = $targetDir . DS . $blockId . '-block.' . $extension;

        if (!copy($sourcePath, $targetPath)) {
            throw new RuntimeException('Could not copy seed image to: ' . $targetPath);
        }
    }

}