<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function snips_install() {
    $cron = cron::byClassAndFunction('snips', 'mqttClient');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('snips');
        $cron->setFunction('mqttClient');
        $cron->setEnable(1);
        $cron->setDeamon(1);
        $cron->setSchedule('* * * * *');
        $cron->setTimeout('1440');
        $cron->save();
    }
    
    $lang = translate::getLanguage();
    if ($lang == 'fr_FR') {
        config::save('defaultTTS', 'Désolé, je ne trouve pas les actions!', 'snips');
    }else if ($lang == 'en_US') {
        config::save('defaultTTS', 'Sorry, I cant find any actions!', 'snips');
    } 
}

function snips_update() {
    $cron = cron::byClassAndFunction('snips', 'mqttClient');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('snips');
        $cron->setFunction('mqttClient');
        $cron->setEnable(1);
        $cron->setDeamon(1);
        $cron->setSchedule('* * * * *');
        $cron->setTimeout('1440');
        $cron->save();
    }
}

function snips_remove() {
    $cron = cron::byClassAndFunction('snips', 'mqttClient');
    if (is_object($cron)) {
        $cron->stop();
        $cron->remove();
    }

    $obj = object::byName('Snips-Intents');
    if (is_object($obj)) {
        $obj->remove();
        snips::debug('[Snips Remove] Removed object: Snips-Intents');
    }

    $eqLogics = eqLogic::byType('snips');
    foreach($eqLogics as $eq) {
        $cmds = snipsCmd::byEqLogicId($eq->getLogicalId);
        foreach($cmds as $cmd) {
            snips::debug('[Snips Remove] Removed slot cmd: '.$cmd->getName());
            $cmd->remove();
        }
        snips::debug('[Snips Remove] Removed intent entity: '.$eq->getName());
        $eq->remove();
    }

    snips::debug('[Snips Remove] Removed Snips Voice assistant!');

    //log::add('snips','info','Suppression extension');
    $resource_path = realpath(dirname(__FILE__) . '/../resources');
    passthru('sudo /bin/bash ' . $resource_path . '/remove.sh ' . $resource_path . ' > ' . log::getPathToLog('SNIPS_dep') . ' 2>&1 &');
    return true;
}

?>
