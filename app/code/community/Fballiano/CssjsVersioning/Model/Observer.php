<?php
/**
 * @category   FBalliano
 * @package    Fballiano_CssjsVersioning
 * @copyright  Copyright (c) Fabrizio Balliano (http://fabrizioballiano.com)
 * @license    https://opensource.org/license/osl-3 Open Software License (OSL 3.0)
 */
class Fballiano_CssjsVersioning_Model_Observer
{
    public const CACHE_ID = 'fballiano_cssjsversioning_cssjsversion';

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function httpResponseSendBefore(Varien_Event_Observer $observer)
    {
        $response = $observer->getResponse();
        $html = $response->getBody();

        // Avoid processing AJAX requests
        if (stripos($html, "</body>") === false) return;

        $version = Mage::app()->loadCache(self::CACHE_ID);

        if (!$version) {
            $baseDir = Mage::getBaseDir();

            // Read the contents of the HEAD file to get the current branch reference
            $headContent = @file_get_contents("{$baseDir}/.git/HEAD");
            if (!$headContent) {
                return;
            }

            // Extract the branch name from the HEAD content
            $parts = explode('/', trim($headContent));
            $branchName = end($parts);
            if (!$branchName) {
                return;
            }

            // Extract last commit hash
            $version = file_get_contents("{$baseDir}/.git/refs/heads/{$branchName}");
            if (!$version) {
                return;
            }

            $version = substr($version, 0, 6); // Only using 6 chars of the hash
            Mage::app()->saveCache($version, self::CACHE_ID, [Mage_Core_Model_Config::CACHE_TAG], 3600); // Cache for 1 hour
        }

        // Process the script tags
        $pattern = '/(<script.+src\s*=\s*["\'])(.*)(["\'].*>)/iU';
        $html = preg_replace_callback($pattern, function($matches) use ($version) {
            $versionParameter = str_contains($matches[2], '?') ? '&v=' : '?v=';
            return $matches[1] . $matches[2] . $versionParameter . $version . $matches[3];
        }, $html);

        // Process the link tags
        $pattern = '/(<link.+href\s*=\s*["\'])(.*)(["\'].*>)/iU';
        $html = preg_replace_callback($pattern, function($matches) use ($version) {
            if (!preg_match('/rel\s*=\s*["\'](icon|stylesheet)["\']/iU', $matches[0])) {
                return $matches[1] . ' ' . $matches[2] . $matches[3];
            }
            $versionParameter = str_contains($matches[2], '?') ? '&v=' : '?v=';
            return $matches[1] . $matches[2] . $versionParameter . $version . $matches[3];
        }, $html);

        $response->setBody($html);
    }
}
