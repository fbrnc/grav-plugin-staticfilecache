<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Collection;
use Grav\Common\Page\Page;
use Grav\Common\Debugger;
use Grav\Common\Taxonomy;
use RocketTheme\Toolbox\Event\Event;

/**
 * Static File Cache Plugin
 *
 * @package Grav\Plugin
 *
 * @author Fabrizio Branca
 * @since 2014-11-27
 */
class StaticfilecachePlugin extends Plugin
{

    public static $writeCache = true;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onOutputRendered' => ['onOutputRendered', 0],
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onPageNotFound' => ['onPageNotFound', 10],
        ];
    }

    /**
     * First possible event:
     * Check if cached content exists and deliver that instead
     */
    public function onPluginsInitialized()
    {
        $fileName = $this->getCacheFilename();
        if (is_file($fileName)) {
            echo file_get_contents($fileName) . '<!-- via event -->';
            exit;
        }
    }

    /**
     * Last possible event:
     * Write generated content to file
     */
    public function onOutputRendered()
    {
        if ($this->isAdmin()) {
            $this->active = false;
        }

        if (!self::$writeCache) {
            return;
        }

        $fileName = $this->getCacheFilename();
        $dir = dirname($fileName);
        $error = false;
        if (!is_dir($dir)) {
            $res = @mkdir($dir, 0775, true);
            if (!$res) {
                $error = true;
            }
        }
        if (!$error) {
            @file_put_contents($fileName, $this->grav->output . '<!-- Generated: ' . date('c') . ' -->');
        }
    }

    /**
     * Get static cache filename based on current request
     *
     * @return string
     */
    protected function getCacheFilename() {

        $fileName = array(
            rtrim(CACHE_DIR, '/'),
            'staticfilecache',
            $this->grav['uri']->host()
        );

        $uri = trim($_SERVER['REQUEST_URI'], '/');
        if ($uri) {
            $fileName[] =  $uri;
        }
        $fileName[] = 'index.html';

        $fileName = implode('/', $fileName);
        return $fileName;
    }

    public function onPageNotFound()
    {
        self::$writeCache = false;
    }

}
