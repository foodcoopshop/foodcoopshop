<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    Configure::read('app.jsNamespace').".Helper.init();"
]);
?>

<h1>Liste deklarationspflichtiger Allergene</h1>


<h2>A</h2>
glutenhaltiges Getreide

<h2>B</h2>
Krebstiere- und -erzeugnisse

<h2>C</h2>
Eier und daraus gewonnene Erzeugnisse

<h2>D</h2>
Fisch- und Fischerzeugnisse (außer Fischgelatine)

<h2>E</h2>
Erdnüsse und –erzeugnisse

<h2>F</h2>
Soja (-bohnen) und –erzeugnisse

<h2>G</h2>
Milch und Milcherzeugnisse (inklusive Laktose)

<h2>H</h2>
Schalenfrüchte und daraus gewonnene Erzeugnisse

<h2>L</h2>
Sellerie und –erzeugnisse

<h2>M</h2>
Senf- und Senferzeugnisse

<h2>N</h2>
Sesam-Samen und –erzeugnisse

<h2>O</h2>
Schwefeldioxid und –erzeugnisse

<h2>P</h2>
Lupinen und daraus hergestellte Produkte

<h2>R</h2>
Weichtiere wie Schnecken, Muscheln, Tintenfische und daraus hergestellte Erzeugnisse

