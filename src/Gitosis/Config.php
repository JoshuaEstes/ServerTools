<?php

namespace Gitosis;

/**
 * Description
 *
 * @package
 * @subpackage
 * @author     Joshua Estes
 * @copyright  2012
 * @version    0.1.0
 * @category
 * @license
 *
 */
class Config {

    /**
     * Path to gitosis.conf file
     *
     * @var string
     */
    protected $config_file;
    /**
     *
     * @var array
     */
    protected $groups;
    /**
     *
     * @var array
     */
    protected $repos;
    /**
     *
     * @var stdClass
     */
    protected $gitosis;

    /**
     * @param string $config_file Path to gitosis.conf
     */
    public function __construct($config_file) {
        $this->config_file = $config_file;
    }

    /**
     * Returns an array of stdClass objects that have the group information
     * 
     * @return array
     */
    public function getGroups() {
        if (null !== $this->groups) {
            return $this->groups;
        }
        $groups = array();
        $current_group = null;
        $handle = \fopen($this->config_file, 'r');
        while (($buffer = \fgets($handle, 4096)) !== false) {
            if (\preg_match("/\[(repo|gitosis|gitweb).*\]/", $buffer)) {
                $current_group = null;
            }

            if (\preg_match("/\[group (.*)\]/", $buffer, $matches)) {
                $group = new \stdClass();
                $group->name = $matches[1];
                $current_group = $group;
                $groups[] = $group;
                unset($matches);
            }

            if (\preg_match("/members = (.*)/", $buffer, $matches) && null !== $current_group) {
                $current_group->members = \explode(" ", $matches[1]);
                unset($matches);
            }

            if (\preg_match("/writable = (.*)/", $buffer, $matches) && null !== $current_group) {
                $current_group->writable = \explode(" ", $matches[1]);
                unset($matches);
            }

            if (\preg_match("/readonly = (.*)/", $buffer, $matches) && null !== $current_group) {
                $current_group->readonly = \explode(" ", $matches[1]);
                unset($matches);
            }
        }
        \fclose($handle);
        return $this->groups = $groups;
    }

    /**
     * 
     * @return array
     */
    public function getRepos() {
        if (null !== $this->repos) {
            return $repos;
        }
        $repos = array();
        $current_repo = null;
        $handle = \fopen($this->config_file, 'r');
        while (($buffer = \fgets($handle, 4096)) !== false) {
            if (\preg_match("/\[(group|gitosis|gitweb).*\]/", $buffer)) {
                $current_repo = null;
            }

            if (\preg_match("/\[repo (.*)\]/", $buffer, $matches)) {
                $repo = new \stdClass();
                $repo->name = $matches[1];
                $current_repo = $repo;
                $repos[] = $repo;
                unset($matches);
            }

            if (\preg_match("/description = (.*)/", $buffer, $matches) && null !== $current_repo) {
                $current_repo->description = $matches[1];
                unset($matches);
            }

            if (\preg_match("/owner = (.*)/", $buffer, $matches) && null !== $current_repo) {
                $current_repo->owner = $matches[1];
                unset($matches);
            }

            if (\preg_match("/daemon = (yes|no)/", $buffer, $matches) && null !== $current_repo) {
                $current_repo->daemon = $matches[1];
                unset($matches);
            }

            if (\preg_match("/gitweb = (yes|no)/", $buffer, $matches) && null !== $current_repo) {
                $current_repo->gitweb = $matches[1];
                unset($matches);
            }
        }
        \fclose($handle);
        return $this->repos = $repos;
    }

    /**
     *
     * @return stdClass
     */
    public function getGitosis() {
        if (null !== $this->gitosis) {
            return $this->gitosis;
        }
        $gitosis = new \stdClass();
        $current_gitosis = $gitosis;
        $handle = \fopen($this->config_file, 'r');
        while (($buffer = \fgets($handle, 4096)) !== false) {
            if (\preg_match("/\[(group|repo|gitweb).*\]/", $buffer)) {
                $current_gitosis = null;
            }

            if (\preg_match("/gitweb = (.*)/", $buffer, $matches) && null !== $current_gitosis) {
                $gitosis->gitweb = $matches[1];
                unset($matches);
            }

            if (\preg_match("/daemon = (.*)/", $buffer, $matches) && null !== $current_gitosis) {
                $gitosis->daemon = $matches[1];
                unset($matches);
            }

            if (\preg_match("/loglevel = (.*)/", $buffer, $matches) && null !== $current_gitosis) {
                $gitosis->loglevel = $matches[1];
                unset($matches);
            }
        }
        \fclose($handle);
        return $this->gitosis = $gitosis;
    }

    /**
     * Add a repo to the gitosis.conf
     *
     * @param string $name
     * @param string $owner
     * @param string $description
     * @param array $options
     */
    public function addRepo($name, $owner=null, $description=null, $options=array()) {
        if (null === $this->repos) {
            $this->getRepos();
        }

        $repo = new \stdObject();
        $repo->name = $name;
        ((null !== $owner) ? $repo->owner = $owner : '');
        ((null !== $description) ? $repo->description = $description : '');
        (isset($options['gitweb']) ? $repo->gitweb = $options['gitweb'] : '');
        (isset($options['daemon']) ? $repo->daemon = $options['daemon'] : '');

        $this->repos[] = $repo;
    }

    public function addGroup() {
        if (null === $this->groups) {
            $this->getGroups();
        }
    }

    public function updateRepo() {
        if (null === $this->repos) {
            $this->getRepos();
        }
    }

    public function updateGroup() {
        if (null === $this->groups) {
            $this->getGroups();
        }
    }

    /**
     * Returns the contents of what should be in the gitosis.conf file
     *
     * @return string
     */
    public function dump() {
        $gitosis = $this->getGitosis();
        $file = array();
        $file[] = '[gitosis]';
        (isset($gitosis->gitweb) ? $file[] = 'gitweb = ' . $gitosis->gitweb : '');
        (isset($gitosis->daemon) ? $file[] = 'daemon' . $gitosis->daemon : '');
        (isset($gitosis->loglevel) ? $file[] = 'loglevel' . $gitosis->loglevel : '');
        $file[] = "";

        foreach ($this->getGroups() as $group) {
            $file[] = '[group ' . $group->name . ']';
            (isset($group->writable) ? $file[] = 'writable = ' . \implode(" ", $group->writable) : '');
            (isset($group->readonly) ? $file[] = 'readonly = ' . \implode(" ", $group->readonly) : '');
            (isset($group->members) ? $file[] = 'members = ' . \implode(" ", $group->members) : '');
            $file[] = "";
        }

        foreach ($this->getRepos() as $repo) {
            $file[] = '[repo ' . $repo->name . ']';
            (isset($repo->description) ? $file[] = 'description = ' . $repo->description : '');
            (isset($repo->owner) ? $file[] = 'owner = ' . $repo->owner : '');
            (isset($repo->daemon) ? $file[] = 'daemon = ' . $repo->daemon : '');
            (isset($repo->gitweb) ? $file[] = 'gitweb = ' . $repo->gitweb : '');
            $file[] = "";
        }

        return trim(\implode("\n", $file));
    }

}