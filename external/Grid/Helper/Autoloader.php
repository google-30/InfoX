<?php
/**
 * Copyright (c) 2010, Liu Detang (liudetang@yahoo.com)
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:

 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the FreeBSD Project.
 */

/**
 * internal autoloader 
 * @author liu.dt, Jun 14, 2010
 *
 */
class Grid_Helper_Autoloader {
    private static $_registed = false;
    private static $_dirs = array();
    
    public static function load($class) {
        $name = str_replace('_', '/', $class);
        $exists = false;
        foreach(self::$_dirs as $d => $prefix) {
            if(empty($prefix)) {
                $file = $d . DIRECTORY_SEPARATOR . $name . '.php';
                $exists = file_exists($file);
            }
            else {
                if(($pos = strpos($class, $prefix)) !== false) {
                    $shortname = str_replace('_', '/', str_replace($prefix, '', $class));
                    $file = $d . DIRECTORY_SEPARATOR . $shortname . '.php';
                    $exists = file_exists($file);
                }
            }
            
            if(true === $exists) {
                include_once($file); 
                return true;
            }
        }
        return false;
    }
    
    public static function registerPath($path, $prefix = null) {
        if(is_string($path)) {
            $d = rtrim($path, DIRECTORY_SEPARATOR);
            if(!empty($prefix)) {
                $prefix = rtrim($prefix, '_').'_';
            }
            if(empty($d)) {
                self::$_dirs[""] = $prefix;
            }
            else {
                self::$_dirs[$d] = $prefix;
            }
        }
        else if(is_array($path)) {
            // <directory> => <prefix>
            foreach($path as $d => $prefix) {
                self::registerPath($d, $prefix);
            }
        }
        
        self::_register();
    }
    
    public static function unregisterPath($path) {
        $d = rtrim($path);
        if(empty($d)) {
            unset(self::$_dirs['~']);
        }
        else {
            unset(self::$_dirs[$d.DIRECTORY_SEPARATOR]);
        }
    }

    private static function _register() {
        if(false === self::$_registed) {
            self::$_registed = true;
            return spl_autoload_register(array(__CLASS__, 'load'));
        }
    }
        
}
