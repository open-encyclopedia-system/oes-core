<?php
/*
 * This file is part of OES, the Open Encyclopedia System.
 *
 * Copyright (C) 2020 Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>

<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class Oes
{

    static $ACTIVATE_LOGGING = true;

    static $LOG_LEVEL = Logger::DEBUG;

    static $DEBUG_CACHING = false;

    static $DEBUG_DTM = false;

    static $DEBUG_INDEXING = false;

    /**
     * @var Logger
     */
    static $logger = false;

    static function init()
    {
        self::init_logging();
    }

    static function init_logging()
    {

        if (!self::$ACTIVATE_LOGGING) {
            return;
        }

        oes_upload_vendor_autoload();

        self::$logger = new Logger('oes');

        self::$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/oes.debug.log', self::$LOG_LEVEL));

        self::$logger->pushHandler(new \Monolog\Handler\SlackWebhookHandler('https://hooks.slack.com/services/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', Logger::CRITICAL));

        self::$logger->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor);

    }

    static function cache_debug($msg, $context)
    {
        if (!self::$DEBUG_CACHING) {
            return;
        }
        self::debug($msg, $context);
    }

    static function debug($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->debug($msg, $context);
    }

    static function dtm_debug($msg, $context)
    {
        if (!self::$DEBUG_DTM) {
            return;
        }
        self::debug($msg, $context);
    }

    static function idx_debug($msg, $context)
    {
        if (!self::$DEBUG_INDEXING) {
            return;
        }
        self::debug($msg, $context);
    }

    static function error($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->error($msg, $context);
    }

    static function warn($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->warning($msg, $context);
    }

    static function info($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->info($msg, $context);
    }

    static function alert($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->alert($msg, $context);
    }

    static function critical($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->critical($msg, $context);
    }

    static function warning($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->warning($msg, $context);
    }

    static function emergency($msg, $context = [])
    {
        if (!self::$ACTIVATE_LOGGING) {
            return;
        }
        self::$logger->emergency($msg, $context);
    }

    


}