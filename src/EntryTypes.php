<?php
/**
* Entry Types plugin for Craft CMS 3.x
*
* Easily switch between entry types in entries section
*
* @link      https://ethercreative.co.uk
* @copyright Copyright (c) 2019 Ether Creative
*/

namespace ether\entrytypes;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\elements\Entry;
use craft\models\Section;
use craft\events\RegisterElementSourcesEvent;

use yii\base\Event;

/**
* Class EntryTypes
*
* @author    Ether Creative
* @package   EntryTypes
* @since     1.0.0
*
*/
class EntryTypes extends Plugin
{
  // Static Properties
  // =========================================================================

  /**
  * @var EntryTypes
  */
  public static $plugin;

  // Public Properties
  // =========================================================================

  /**
  * @var string
  */
  public $schemaVersion = '1.0.0';

  // Public Methods
  // =========================================================================

  /**
  * @inheritdoc
  */
  public function init()
  {
    parent::init();
    self::$plugin = $this;

    Event::on(Entry::class, Element::EVENT_REGISTER_SOURCES, function(RegisterElementSourcesEvent $event) {
      $children = [];

      foreach ($event->sources as $i => $source) {
        if (!isset($source['data'])) {
          continue;
        }

        $children[$i] = [];
        $entryTypes = Craft::$app->sections->getEntryTypesBySectionId($source['criteria']['sectionId']);

        foreach ($entryTypes as $entryType) {
          $children[$i][] = [
            'key' => 'section:' . $entryType->uid,
            'label' => $entryType->name,
            'data' => [
              'has-structure' => true,
              'default-sort' => 'structure:asc',
              'type' => 'structure',
              'handle' => $entryType->handle,
              'entry-type' => true
            ],
            'criteria' => [
              'sectionId' => $entryType->sectionId,
              'type' => $entryType->handle,
              'editable' => false,
              ]
            ];
          }
        }

        foreach ($children as $key => $child) {
          $event->sources[$key]['nested'] = $child;
        }
      });
    }
  }
