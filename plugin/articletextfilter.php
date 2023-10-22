<?php
/**
 * @copyright	Copyright (c)2012 Open Source Design / opensourcedesign.nl
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;


class PlgContentArticleTextFilter extends CMSPlugin
{
    private $user_id = -1;
    private $article = null;
    private $view_levels = null;
    private $groups = null;
    private $processingMode = 'group';

    /**
     * Gets the view level ID out of a view level title. If an ID was passed,
     * it simply returns the ID. If a non-existent view level is passed, it returns -1.
     *
     * @param $title string|int The view level title or ID
     *
     * @return int The view level ID
     */
    private function getViewLevelId($title) {
        // Don't process invalid titles
        if (empty($title)) {
            return -1;
        }

        // Fetch a list of view acces levels if we haven't done so already
        if (is_null($this->view_levels)) {
            $this->view_levels = array();

            // Get a database object.
            $db = Factory::getDbo();

            // Build the base query.
            $query = new \Joomla\CMS\Database\Query($db);
            $query->select('id, title');
            $query->from($query->qn('#__viewlevels'));

            // Set the query for execution.
            $db->setQuery((string) $query);

            // Build the view levels array.
            foreach ($db->loadObjectList() as $level) {
                $this->view_levels[strtoupper($level->title)] = $level->id;
            }
        }

        $title = strtoupper($title);
        if (array_key_exists($title, $this->view_levels)) {
            // Mapping found
            return $this->view_levels[$title];
        } elseif(ctype_digit($title)) {
            // Numeric ID passed
            return (int)$title;
        } else {
            // No match!
            return -1;
        }
    }

    /**
     * Gets the group ID out of a group title. If an ID was passed, it simply returns the ID.
     * If a non-existent group is passed, it returns -1.
     *
     * @param $title string|int The group title or ID
     *
     * @return int The group ID
     */
    private function getUserGroupId($title) {
        // Don't process invalid titles
        if (empty($title)) {
            return -1;
        }

        // Fetch a list of view acces levels if we haven't done so already
        if (is_null($this->groups)) {
            $this->groups = array();

            // Get a database object.
            $db = Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseDriver::class);

            // Build the base query.
            $query = $db->getQuery($db);
            $query->select('id, title');
            $query->from($query->qn('#__usergroups'));

            // Set the query for execution.
            $db->setQuery((string) $query);

            // Build the view levels array.
            foreach ($db->loadObjectList() as $level) {
                $this->groups[strtoupper($level->title)] = $level->id;
            }
        }

        $title = strtoupper($title);
        if (array_key_exists($title, $this->groups)) {
            // Mapping found
            return $this->groups[$title];
        } elseif(ctype_digit($title)) {
            // Numeric ID passed
            return (int)$title;
        } else {
            // No match!
            return -1;
        }
    }

    /**
     * Checks if a user has access to a particular view level.
     *
     * @param $id int The view level ID
     *
     * @return bool True if the author has access.
     */
    private function hasViewAccess($view_level_id)
    {
        // Don't process empty or invalid IDs
        $view_level_id = trim($view_level_id);
        if (empty($view_level_id) || (($view_level_id <= 0) && ($view_level_id != '*'))) {
            return false;
         }

        $user = Factory::getUser($this->user_id);
        $authorised_levels = $user->getAuthorisedViewLevels();

        if (!isset($authorised_levels)) {
            return false;
        }

        if($view_level_id == '*') {
            return true;
        } else {
            return in_array($view_level_id, $authorised_levels);
        }
    }

    /**
     * Checks if a user is member of a particular user group.
     *
     * @param $id int The view level ID
     *
     * @return bool True if the author has access.
     */
    private function isInGroup($group_id)
    {
        // Don't process empty or invalid IDs
        $group_id = trim($group_id);
        if (empty($group_id) || (($group_id <= 0) && ($group_id != '*'))) {
            return false;
         }

        $user = Factory::getUser($this->user_id);
        $authorised_groups = $user->getAuthorisedGroups();

        if (!isset($authorised_groups)) {
            return false;
        }

        if($group_id == '*') {
            return true;
        } else {
            return in_array($group_id, $authorised_groups);
        }
    }

    /**
     * preg_match callback to process each match
     */
    private function process($match)
    {
        $ret = '';

        if ($this->analyze($match[1])) {
            $ret = $match[2];
        }

        return $ret;
    }

    /**
     * Analyzes a filter statement and decides if it's true or not
     *
     * @return boolean
     */
    private function analyze($statement)
    {
        $ret = false;
        if ($this->user_id > 0) {
            if ($statement) {
                // Stupid, stupid crap... ampersands replaced by &amp;...
                $statement = str_replace('&amp;&amp;', '&&', $statement);
                // First, break down to OR statements
                $items = explode("||", trim($statement));
                for ($i=0; $i<count($items) && !$ret; $i++) {
                    // Break down AND statements
                    $expression = trim($items[$i]);
                    $subitems = explode('&&', $expression);
                    $ret = true;

                    foreach($subitems as $item) {
                        $item = trim($item);
                        $negate = false;
                        if(substr($item,0,1) == '!') {
                            $negate = true;
                            $item = substr($item,1);
                            $item = trim($item);
                        }
                        $id = trim($item);
                        switch ($this->processingMode) {
                            case 'user':
                                $result = ($id == '*') ? true : $id == $this->user_id;
                                break;

                            case 'group':
                                if ($id != '*') {
//									$id = $this->getViewLevelId($id);
                                    $id = $this->getUserGroupId($id);
                                }
//								$result = $this->hasViewAccess($id);
                                $result = $this->isInGroup($id);
                                break;
                        }
                        $ret = $ret && ($negate ? !$result : $result);
                    }
                }
            }
        }

        return $ret;
    }

    /*
     * Any content between the tags will (not) be rendered
     * if the author of the article is (not) in any of the listed groups.
     */
    private function processAuthorGroup($text) {
        $this->user_id = (isset($this->article->created_by) ? $this->article->created_by : 0);
        $this->processingMode = 'group';

        // Search for this tag in the content
        $regex = "#{author_group\s+(.*?)}(.*?){/author_group}#s";

        $text = preg_replace_callback($regex, array($this, 'process'), $text);

        return $text;
    }

    /*
     * Any content between the tags will (not) be rendered
     * if the logged in user is (not) in any of the listed groups.
     */
    private function processUserGroup($text) {
        $this->user_id = Factory::getUser()->id;
        $this->processingMode = 'group';

        // Search for this tag in the content
        $regex = "#{user_group\s+(.*?)}(.*?){/user_group}#s";

        $text = preg_replace_callback($regex, array($this, 'process'), $text);

        return $text;
    }

    /*
     * Any content between the tags will (not) be rendered
     * if the logged in user is (not) the author of the article.
     */
    private function processAuthor($text) {
        // Search for this tag in the content
        $regex = "#{author}(.*?){/author}#s";

        $userId = Factory::getUser()->id;
        $isAuthor = (isset($this->article->created_by) && $this->article->created_by == Factory::getUser()->id ? true : false);

        $text = preg_replace_callback($regex, function ($match) use ($isAuthor) {
            return $isAuthor ? $match[1] : '';
        }, $text);

        return $text;
    }

    /*
     * Any content between the tags will (not) be rendered
     * if the logged in user is (not) one of the listed users.
     */
    private function processLoggedInUser($text) {
        $this->user_id = Factory::getUser()->id;
        $this->processingMode = 'user';

        // Search for this tag in the content
        $regex = "#{user\s+(.*?)}(.*?){/user}#s";

        $text = preg_replace_callback($regex, array($this, 'process'), $text);

        return $text;
    }

    /*
     * Any content between the tags will be rendered
     * only if the article is a featured article.
     */
    private function processFeatured($text) {
        // Search for this tag in the content
        $regex = "#{featured}(.*?){/featured}#s";
        $isFeatured = (isset($this->article->featured) && $this->article->featured == '1' ? true : false);

        $text = preg_replace_callback($regex, function ($match) use ($isFeatured) {
            return $isFeatured ? $match[1] : '';
        }, $text);

        return $text;
    }

    /*
     * Any content between the tags will be rendered
     * only if the article is displayed on the home page.
     */
    private function processHomePage($text) {
        // Search for this tag in the content
        $regex = "#{(!?)homepage}(.*?){/homepage}#s";
        $menu = Factory::getApplication()->getMenu();
        $lang = Factory::getLanguage();
        $isHome = $menu->getActive() == $menu->getDefault($lang->getTag());

        $text = preg_replace_callback($regex, function ($match) use ($isHome) {
            if ($match[1]) {
                return $isHome ? '' : $match[2];
            } else {
                return $isHome ? $match[2] : '';
            }
        }, $text);

        return $text;
    }

    /*
     * Any content between the tags will be rendered
     * only if the user is a guest.
     */
    private function processGuest($text) {
        // Search for this tag in the content
        $regex = "#{(!?)guest}(.*?){/guest}#s";
        $isGuest = Factory::getUser()->guest;

        $text = preg_replace_callback($regex, function ($match) use ($isGuest) {
            if ($match[1]) {
                return $isGuest ? '' : $match[2];
            } else {
                return $isGuest ? $match[2] : '';
            }
        }, $text);

        return $text;
    }

    public function onContentPrepare($context, &$article, &$params, $page=0) {
        if (!isset($article->text)) {
            return '';
        }

        $this->article = $article;

        $text = $article->text;

        if (!empty($text)) {
            // Check whether the plugin should process or not
            if (StringHelper::strpos($text, '{author_group') !== false) {
                $text = $this->processAuthorGroup($text);
            }

            if (StringHelper::strpos($text, '{user_group') !== false) {
                $text = $this->processUserGroup($text);
            }

            if (StringHelper::strpos($text, '{author') !== false) {
                $text = $this->processAuthor($text);
            }

            if (StringHelper::strpos($text, '{user') !== false) {
                $text = $this->processLoggedInUser($text);
            }

            if (StringHelper::strpos($text, '{featured') !== false) {
                $text = $this->processFeatured($text);
            }

            if (StringHelper::strpos($text, '{homepage') !== false || StringHelper::strpos($text, '{!homepage')) {
                $text = $this->processHomePage($text);
            }

            if (StringHelper::strpos($text, '{guest') !== false || StringHelper::strpos($text, '{!guest')) {
                $text = $this->processGuest($text);
            }

            $article->text = $text;
        }

        return '';
    }
}
