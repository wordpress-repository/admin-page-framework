<?php 
/**
	Admin Page Framework v3.8.9b04 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/admin-page-framework>
	Copyright (c) 2013-2016, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
class AdminPageFramework_Debug_Base extends AdminPageFramework_FrameworkUtility {
    static protected function _getLegibleDetails($mValue) {
        if (is_array($mValue)) {
            return '(array, length: ' . count($mValue) . ') ' . print_r(self::_getLegibleArray($mValue), true);
        }
        return print_r(self::_getLegibleValue($mValue), true);
    }
    static protected function _getLegible($mValue) {
        $mValue = is_object($mValue) ? (method_exists($mValue, '__toString') ? ( string )$mValue : ( array )$mValue) : $mValue;
        $mValue = is_array($mValue) ? self::_getArrayMappedRecursive(self::_getSlicedByDepth($mValue, 10), array(__CLASS__, '_getObjectName')) : $mValue;
        return self::_getArrayRepresentationSanitized(print_r($mValue, true));
    }
    static private function _getObjectName($mItem) {
        if (is_object($mItem)) {
            return '(object) ' . get_class($mItem);
        }
        return $mItem;
    }
    static private function _getLegibleCallable($asoCallable) {
        return '(callable) ' . self::_getCallableName($asoCallable);
    }
    static public function _getCallableName($asoCallable) {
        if (is_string($asoCallable)) {
            return $asoCallable;
        }
        if (is_object($asoCallable)) {
            return get_class($asoCallable);
        }
        $_sSubject = is_object($asoCallable[0]) ? get_class($asoCallable[0]) : ( string )$asoCallable[0];
        return $_sSubject . '::' . ( string )$asoCallable[1];
    }
    static public function _getLegibleObject($oObject) {
        if (method_exists($oObject, '__toString')) {
            return ( string )$oObject;
        }
        return '(object) ' . get_class($oObject) . ' ' . count(get_object_vars($oObject)) . ' properties.';
    }
    static public function _getLegibleArray(array $aArray) {
        return self::_getArrayMappedRecursive(self::_getSlicedByDepth($aArray, 10), array(__CLASS__, '_getLegibleValue'));
    }
    static private function _getLegibleValue($mItem) {
        if (is_callable($mItem)) {
            return self::_getLegibleCallable($mItem);
        }
        return is_scalar($mItem) ? self::_getLegibleScalar($mItem) : self::_getLegibleNonScalar($mItem);
    }
    static private function _getLegibleNonScalar($mNonScalar) {
        $_sType = gettype($mNonScalar);
        if (is_null($mNonScalar)) {
            return '(null)';
        }
        if (is_object($mNonScalar)) {
            return '(' . $_sType . ') ' . get_class($mNonScalar);
        }
        if (is_array($mNonScalar)) {
            return '(' . $_sType . ') ' . count($mNonScalar) . ' elements';
        }
        return '(' . $_sType . ') ' . ( string )$mNonScalar;
    }
    static private function _getLegibleScalar($sScalar) {
        if (is_bool($sScalar)) {
            return '(boolean) ' . ($sScalar ? 'true' : 'false');
        }
        return is_string($sScalar) ? self::_getLegibleString($sScalar) : '(' . gettype($sScalar) . ', length: ' . self::_getValueLength($sScalar) . ') ' . $sScalar;
    }
    static private function _getValueLength($mValue) {
        $_sVariableType = gettype($mValue);
        if (in_array($_sVariableType, array('string', 'integer'))) {
            return strlen($mValue);
        }
        if ('array' === $_sVariableType) {
            return count($mValue);
        }
        return null;
    }
    static private function _getLegibleString($sString, $iCharLimit = 200) {
        static $_iMBSupport;
        $_iMBSupport = isset($_iMBSupport) ? $_iMBSupport : ( integer )function_exists('mb_strlen');
        $_aStrLenMethod = array('strlen', 'mb_strlen');
        $_aSubstrMethod = array('substr', 'mb_substr');
        $_iCharLength = call_user_func_array($_aStrLenMethod[$_iMBSupport], array($sString));
        return $_iCharLength <= $iCharLimit ? '(string, length: ' . $_iCharLength . ') ' . $sString : '(string, length: ' . $_iCharLength . ') ' . call_user_func_array($_aSubstrMethod[$_iMBSupport], array($sString, 0, $iCharLimit)) . '...';
    }
    static protected function _getArrayRepresentationSanitized($sString) {
        $sString = preg_replace('/\)(\r\n?|\n)(?=(\r\n?|\n)\s+[\[\)])/', ')', $sString);
        $sString = preg_replace('/Array(\r\n?|\n)\s+\((\r\n?|\n)\s+\)/', 'Array()', $sString);
        return $sString;
    }
    static private function _getSlicedByDepth(array $aSubject, $iDepth = 0) {
        foreach ($aSubject as $_sKey => $_vValue) {
            if (is_array($_vValue)) {
                $_iDepth = $iDepth;
                if ($iDepth > 0) {
                    $aSubject[$_sKey] = self::_getSlicedByDepth($_vValue, --$iDepth);
                    $iDepth = $_iDepth;
                    continue;
                }
                unset($aSubject[$_sKey]);
            }
        }
        return $aSubject;
    }
    static private function _getArrayMappedRecursive(array $aArray, $oCallable) {
        self::$_oCurrentCallableForArrayMapRecursive = $oCallable;
        $_aArray = array_map(array(__CLASS__, '_getArrayMappedNested'), $aArray);
        self::$_oCurrentCallableForArrayMapRecursive = null;
        return $_aArray;
    }
    static private $_oCurrentCallableForArrayMapRecursive;
    static private function _getArrayMappedNested($mItem) {
        return is_array($mItem) ? array_map(array(__CLASS__, '_getArrayMappedNested'), $mItem) : call_user_func(self::$_oCurrentCallableForArrayMapRecursive, $mItem);
    }
}
class AdminPageFramework_Debug_Log extends AdminPageFramework_Debug_Base {
    static protected function _log($mValue, $sFilePath = null) {
        static $_fPreviousTimeStamp = 0;
        $_oCallerInfo = debug_backtrace();
        $_sCallerFunction = self::getElement($_oCallerInfo, array(1, 'function'), '');
        $_sCallerClass = self::getElement($_oCallerInfo, array(1, 'class'), '');
        $_fCurrentTimeStamp = microtime(true);
        file_put_contents(self::_getLogFilePath($sFilePath, $_sCallerClass), self::_getLogHeadingLine($_fCurrentTimeStamp, round($_fCurrentTimeStamp - $_fPreviousTimeStamp, 3), $_sCallerClass, $_sCallerFunction) . PHP_EOL . self::_getLegibleDetails($mValue) . PHP_EOL . PHP_EOL, FILE_APPEND);
        $_fPreviousTimeStamp = $_fCurrentTimeStamp;
    }
    static private function _getLogFilePath($bsFilePath, $sCallerClass) {
        $_bFileExists = self::_createFile($bsFilePath);
        if ($_bFileExists) {
            return $bsFilePath;
        }
        if (true === $bsFilePath) {
            return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . basename(get_class()) . '_' . date("Ymd") . '.log';
        }
        return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . basename(get_class()) . '_' . basename($sCallerClass) . '_' . date("Ymd") . '.log';
    }
    static private function _createFile($sFilePath) {
        if (!$sFilePath || true === $sFilePath) {
            return false;
        }
        if (file_exists($sFilePath)) {
            return true;
        }
        $_bhResrouce = fopen($sFilePath, 'w');
        return ( boolean )$_bhResrouce;
    }
    static private function _getLogHeadingLine($fCurrentTimeStamp, $nElapsed, $sCallerClass, $sCallerFunction) {
        $_nGMTOffset = self::_getSiteGMTOffset();
        $_iPageLoadID = self::_getPageLoadID();
        $_nNow = $fCurrentTimeStamp + ($_nGMTOffset * 60 * 60);
        $_nMicroseconds = str_pad(round(($_nNow - floor($_nNow)) * 10000), 4, '0');
        $_aOutput = array(date("Y/m/d H:i:s", $_nNow) . '.' . $_nMicroseconds, self::_getFormattedElapsedTime($nElapsed), $_iPageLoadID, AdminPageFramework_Registry::getVersion(), $sCallerClass . '::' . $sCallerFunction, current_filter(), self::getCurrentURL(),);
        return implode(' ', $_aOutput);
    }
    static private function _getSiteGMTOffset() {
        static $_nGMTOffset;
        $_nGMTOffset = isset($_nGMTOffset) ? $_nGMTOffset : get_option('gmt_offset');
        return $_nGMTOffset;
    }
    static private function _getPageLoadID() {
        static $_iPageLoadID;
        $_iPageLoadID = $_iPageLoadID ? $_iPageLoadID : uniqid();
        return $_iPageLoadID;
    }
    static private function _getFormattedElapsedTime($nElapsed) {
        $_aElapsedParts = explode(".", ( string )$nElapsed);
        $_sElapsedFloat = str_pad(self::getElement($_aElapsedParts, 1, 0), 3, '0');
        $_sElapsed = self::getElement($_aElapsedParts, 0, 0);
        $_sElapsed = strlen($_sElapsed) > 1 ? '+' . substr($_sElapsed, -1, 2) : ' ' . $_sElapsed;
        return $_sElapsed . '.' . $_sElapsedFloat;
    }
}
class AdminPageFramework_Debug extends AdminPageFramework_Debug_Log {
    static public function dump($asArray, $sFilePath = null) {
        echo self::get($asArray, $sFilePath);
    }
    static public function getDetails($mValue, $bEscape = true) {
        $_sValueWithDetails = self::_getArrayRepresentationSanitized(self::_getLegibleDetails($mValue));
        return $bEscape ? "<pre class='dump-array'>" . htmlspecialchars($_sValueWithDetails) . "</pre>" : $_sValueWithDetails;
    }
    static public function get($asArray, $sFilePath = null, $bEscape = true) {
        if ($sFilePath) {
            self::log($asArray, $sFilePath);
        }
        return $bEscape ? "<pre class='dump-array'>" . htmlspecialchars(self::_getLegible($asArray)) . "</pre>" : self::_getLegible($asArray);
    }
    static public function log($mValue, $sFilePath = null) {
        self::_log($mValue, $sFilePath);
    }
    static public function dumpArray($asArray, $sFilePath = null) {
        self::showDeprecationNotice('AdminPageFramework_Debug::' . __FUNCTION__, 'AdminPageFramework_Debug::dump()');
        AdminPageFramework_Debug::dump($asArray, $sFilePath);
    }
    static public function getArray($asArray, $sFilePath = null, $bEscape = true) {
        self::showDeprecationNotice('AdminPageFramework_Debug::' . __FUNCTION__, 'AdminPageFramework_Debug::get()');
        return AdminPageFramework_Debug::get($asArray, $sFilePath, $bEscape);
    }
    static public function logArray($asArray, $sFilePath = null) {
        self::showDeprecationNotice('AdminPageFramework_Debug::' . __FUNCTION__, 'AdminPageFramework_Debug::log()');
        AdminPageFramework_Debug::log($asArray, $sFilePath);
    }
    static public function getAsString($mValue) {
        self::showDeprecationNotice('AdminPageFramework_Debug::' . __FUNCTION__);
        return self::_getLegible($mValue);
    }
}
