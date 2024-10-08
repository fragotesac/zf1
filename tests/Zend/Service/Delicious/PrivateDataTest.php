<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_Delicious
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_Delicious
 */
require_once 'Zend/Service/Delicious.php';


/**
 * @category   Zend_Service
 * @package    Zend_Service_Delicious
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Service
 * @group      Zend_Service_Delicious
 */
class Zend_Service_Delicious_PrivateDataTest extends PHPUnit\Framework\TestCase
{
    const TEST_UNAME = 'zfTestUser';
    const TEST_PASS  = 'zfuser';

    private static $TEST_POST_TITLE  = 'test - title';
    private static $TEST_POST_URL    = 'http://zfdev.com/unittests/delicious/test_url_1';
    private static $TEST_POST_NOTES  = 'test - note';
    private static $TEST_POST_TAGS   = array('testTag1','testTag2');
    private static $TEST_POST_SHARED = false;

    /**
     * @var Zend_Service_Delicious
     */
    protected $_delicious;

    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        if (!(defined('TESTS_ZEND_SERVICE_DELICIOUS_ENABLED') &&
            constant('TESTS_ZEND_SERVICE_DELICIOUS_ENABLED'))) {
            $this->markTestSkipped('Zend_Service_Delicious tests not enabled in TestConfiguration.php');
        }

        $httpClient = new Zend_Http_Client();
        $httpClient->setConfig(array(
                'useragent' => 'Zend_Service_Delicious - Unit tests/0.1',
                'keepalive' => true
        ));
        Zend_Rest_Client::setHttpClient($httpClient);

        $this->_delicious = new Zend_Service_Delicious(self::TEST_UNAME, self::TEST_PASS);
    }

    /**
     *
     * @return void
     */
    public function testLastUpdate()
    {
        $this->assertTrue($this->_delicious->getLastUpdate() instanceof Zend_Date);
    }

    /**
     *
     * @return void
     */
    public function testTagsAndBundles()
    {
        // get tags
        $tags = $this->_delicious->getTags();
        $this->assertIsArray($tags);
        $tags = array_keys($tags);

        if (count($tags) < 1) {
            $this->fail('Test account corrupted - no tags');
        }

        $oldTagName = $tags[0];
        $newTagName = uniqid('tag');

        // rename tag
        $this->_delicious->renameTag($oldTagName, $newTagName);

        sleep(15);

        // get renamed tags
        $tags = $this->_delicious->getTags();

        $this->assertArrayHasKey($newTagName, $tags);
        $this->assertArrayNotHasKey($oldTagName, $tags);

        $tags = array_keys($tags);

        // add new bundle
        $newBundleName = uniqid('bundle');
        $this->_delicious->addBundle($newBundleName, $tags);

        sleep(15);

        // check if bundle was added
        $bundles = $this->_delicious->getBundles();
        $this->assertIsArray($bundles);
        $this->assertArrayHasKey($newBundleName, $bundles);
        $this->assertEquals($tags, $bundles[$newBundleName]);

        // delete bundle
        $this->_delicious->deleteBundle($newBundleName);

        sleep(15);

        // check if bundle was deleted
        $bundles = $this->_delicious->getBundles();
        $this->assertArrayNotHasKey($newBundleName, $bundles);
    }

    /**
     *
     * @return void
     */
    public function _testAddDeletePost()
    {
        $newPost = $this->_delicious->createNewPost(self::$TEST_POST_TITLE, self::$TEST_POST_URL)
                                    ->setNotes(self::$TEST_POST_NOTES)
                                    ->setTags(self::$TEST_POST_TAGS)
                                    ->setShared(self::$TEST_POST_SHARED);

        // check if post was created correctly
        $this->assertEquals(self::$TEST_POST_TITLE, $newPost->getTitle());
        $this->assertEquals(self::$TEST_POST_URL, $newPost->getUrl());
        $this->assertEquals(self::$TEST_POST_NOTES, $newPost->getNotes());
        $this->assertEquals(self::$TEST_POST_TAGS, $newPost->getTags());
        $this->assertEquals(self::$TEST_POST_SHARED, $newPost->getShared());

        // test tag adding to tag
        $newTagName = uniqid('tag');
        $newPost->addTag($newTagName);
        $this->assertStringContainsString($newTagName, $newPost->getTags());

        // test tag removeing
        $newPost->removeTag($newTagName);
        $this->assertStringNotContainsString($newTagName, $newPost->getTags());

        // send post to del.icio.us
        $newPost->save();

        sleep(15);

        // get the post back
        $returnedPosts = $this->_delicious->getPosts(null, null, self::$TEST_POST_URL);

        $this->assertCount(1, $returnedPosts);

        $savedPost = $returnedPosts[0];

        // check if post was saved correctly
        $this->assertEquals(self::$TEST_POST_TITLE, $savedPost->getTitle());
        $this->assertEquals(self::$TEST_POST_URL, $savedPost->getUrl());
        $this->assertEquals(self::$TEST_POST_NOTES, $savedPost->getNotes());
        $this->assertEquals(self::$TEST_POST_TAGS, $savedPost->getTags());
        $this->assertEquals(self::$TEST_POST_SHARED, $savedPost->getShared());
        $this->assertTrue($savedPost->getDate() instanceof Zend_Date);
        $this->assertIsString($savedPost->getHash());
        $this->assertIsInt($savedPost->getOthers());

        // delete post
        $savedPost->delete();

        sleep(15);

        // check if post was realy deleted
        $returnedPosts = $this->_delicious->getPosts(null, null, self::$TEST_POST_URL);
        $this->assertCount(0, $returnedPosts);
    }

    /**
     * Ensures that getAllPosts() provides expected behavior
     *
     * @return void
     */
    public function testGetAllPosts()
    {
        $posts = $this->_delicious->getAllPosts('zfSite');
        $this->assertInstanceOf(Zend_Service_Delicious_PostList::class, $posts);

        foreach ($posts as $post) {
            $this->assertStringContainsString('zfSite', $post->getTags());
        }
    }

    /**
     * Ensures that getRecentPosts() provides expected behavior
     *
     * @return void
     */
    public function testGetRecentPosts()
    {
        $posts = $this->_delicious->getRecentPosts('zfSite', 10);
        $this->assertInstanceOf(Zend_Service_Delicious_PostList::class, $posts);
        $this->assertTrue(count($posts) <= 10);

        foreach ($posts as $post) {
            $this->assertStringContainsString('zfSite', $post->getTags());
        }
    }

    /**
     * Ensures that getPosts() provides expected behavior
     *
     * @return void
     */
    public function testGetPosts()
    {
        $posts = $this->_delicious->getPosts('zfSite', new Zend_Date(), 'help');
        $this->assertInstanceOf(Zend_Service_Delicious_PostList::class, $posts);
        $this->assertTrue(count($posts) <= 10);

        foreach ($posts as $post) {
            $this->assertStringContainsString('zfSite', $post->getTags());
        }
    }

    /**
     *
     * @return void
     */
    public function testDates()
    {
        $this->assertIsArray($this->_delicious->getDates());
    }
}
