<?php

namespace Test\Gitosis;

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
use Symfony\Component\Filesystem\Filesystem;
use Gitosis\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase {

    protected $tmp_folder = '/tmp';
    protected $gitosis_conf;
    protected $gitosis_conf_tmpl;

    /**
     * Create the gitosis.conf file
     */
    protected function setUp() {
        $this->gitosis_conf = $this->tmp_folder . '/' . \time() . '.conf';
        $this->gitosis_conf_tmpl = <<<EOF
[gitosis]
gitweb = yes

[group devs]
members = user1 user2

[group admins]
members = user1

[group gitosis-admin]
writable = gitosis-admin
members = @admins

[group foobar]
writable = foobar
members = @devs

[group myteam]
writable = free_monkey
members = jdoe

[repo foobar]
description = Git repository for foobar
owner = user
EOF;
        \file_put_contents($this->gitosis_conf, $this->gitosis_conf_tmpl);
    }

    /**
     * Delete the test gitosis.conf file
     */
    protected function tearDown() {
        \unlink($this->gitosis_conf);
    }

    /**
     * Test to make sure the correct information is returned
     */
    public function testGetGroups() {
        $config = new Config($this->gitosis_conf);
        $actual = $config->getGroups();

        $expected = array();

        $devs = new \stdClass();
        $devs->name = 'devs';
        $devs->members = array('user1', 'user2');
        $expected[] = $devs;

        $admins = new \stdClass();
        $admins->name = 'admins';
        $admins->members = array('user1');
        $expected[] = $admins;

        $gitosis_admin = new \stdClass();
        $gitosis_admin->name = 'gitosis-admin';
        $gitosis_admin->members = array('@admins');
        $gitosis_admin->writable = array('gitosis-admin');
        $expected[] = $gitosis_admin;

        $foobar = new \stdClass();
        $foobar->name = 'foobar';
        $foobar->members = array('@devs');
        $foobar->writable = array('foobar');
        $expected[] = $foobar;

        $myteam = new \stdClass();
        $myteam->name = 'myteam';
        $myteam->members = array('jdoe');
        $myteam->writable = array('free_monkey');
        $expected[] = $myteam;

        $this->assertEquals($expected, $actual);
    }

    public function testGetRepos() {
        $config = new Config($this->gitosis_conf);
        $actual = $config->getRepos();

        $expected = array();

        $foobar = new \stdClass();
        $foobar->name = 'foobar';
        $foobar->description = "Git repository for foobar";
        $foobar->owner = "user";
        $expected[] = $foobar;

        $this->assertEquals($expected, $actual);
    }

    public function testGetGitosis() {
        $config = new Config($this->gitosis_conf);
        $actual = $config->getGitosis();

        $expected = new \stdClass();
        $expected->gitweb = 'yes';

        $this->assertEquals($expected, $actual);
    }

    public function testDump() {
        $config = new Config($this->gitosis_conf);
        $actual = $config->dump();

        $expected = $this->gitosis_conf_tmpl;

        $this->assertEquals($expected, $actual);
    }

}