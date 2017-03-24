<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Controller\Context;
use MakinaCorpus\Layout\Controller\DefaultTokenGenerator;
use MakinaCorpus\Layout\Controller\EditToken;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestLayout;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestLayoutStorage;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestTokenLayoutStorage;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Error\InvalidTokenError;

/**
 * Basic context and token testing
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    protected function assertTokenValues(EditToken $token, array $layouts)
    {
        $this->assertTrue($token->contains($layouts[1]));
        $this->assertTrue($token->contains($layouts[2]));
        $this->assertFalse($token->contains($layouts[3]));
        $this->assertFalse($token->contains($layouts[4]));
        $this->assertFalse($token->contains($layouts[5]));
        $this->assertFalse($token->contains($layouts[6]));

        $this->assertSame('7', $token->getValue('user_id'));
        $this->assertSame('bar', $token->getValue('foo'));
        $this->assertSame('', $token->getValue('null'));
        $this->assertSame('', $token->getValue('non_existing'));

        $this->assertTrue($token->matchValue('user_id', 7));
        $this->assertFalse($token->matchValue('user_id', 0));
        $this->assertFalse($token->matchValue('user_id', ''));
        $this->assertFalse($token->matchValue('user_id', 12));

        $this->assertTrue($token->matchValue('foo', 'bar'));
        $this->assertFalse($token->matchValue('foo', ''));
        $this->assertFalse($token->matchValue('foo', 0));
        $this->assertFalse($token->matchValue('foo', null));

        $this->assertTrue($token->matchValue('null', ''));
        $this->assertFalse($token->matchValue('null', 0));
        $this->assertFalse($token->matchValue('null', null));
        $this->assertFalse($token->matchValue('null', 'null'));

        $this->assertFalse($token->matchValue('non_existing', 'a_value'));
        $this->assertFalse($token->matchValue('non_existing', null));

        $this->assertCount(2, $token->getLayoutIdList());
        $this->assertArraySubset([1, 2], $token->getLayoutIdList());
    }

    /**
     * Create an empty testing token
     *
     * @return Context
     */
    private function createContext() : Context
    {
        $storage = new TestLayoutStorage();
        $tokenStorage = new TestTokenLayoutStorage();
        $tokenGenerator = new DefaultTokenGenerator();
        $context = new Context($storage, $tokenStorage, $tokenGenerator);

        return $context;
    }

    /**
     * Basic functionnality
     */
    public function testEditToken()
    {
        $context = $this->createContext();

        // Just for the sake of rising the coverage
        $this->assertInstanceOf(DefaultTokenGenerator::class, $context->getTokenGenerator());
        $this->assertTrue($context->isEmpty());
        $this->assertFalse($context->hasToken());

        // Now the real tests shall begin
        $layouts = [];
        $layouts[1] = $layout1 = new TestLayout(1);
        $layouts[2] = $layout2 = new TestLayout(2);
        $layouts[3] = $layout3 = new TestLayout(3);
        $layouts[4] = $layout4 = new TestLayout(4);
        $layouts[5] = $layout5 = new TestLayout(5);
        $layouts[6] = $layout6 = new TestLayout(6);

        $context->add([$layout1, $layout2], true);
        $this->assertFalse($context->isEmpty());
        $context->add([$layout3, $layout4, $layout5], false);

        $this->assertTrue($context->isEditable($layout1));
        $this->assertTrue($context->isEditable($layout2));
        $this->assertFalse($context->isEditable($layout3));
        $this->assertFalse($context->isEditable($layout4));
        $this->assertFalse($context->isEditable($layout5));
        $this->assertFalse($context->isEditable($layout6));

        $token = $context->createEditToken([
            'user_id' => 7,
            'foo'     => 'bar',
            'null'    => null,
        ]);
        $this->assertTokenValues($token, $layouts);

        /** @var \MakinaCorpus\Layout\Controller\EditToken $wakeUpToken */
        $wakeUpToken = unserialize(serialize($token));
        $this->assertTokenValues($wakeUpToken, $layouts);
        $this->assertSame($token->getToken(), $wakeUpToken->getToken());

        $all = $context->getAll();
        $this->assertCount(5, $all);
        $this->assertSame($layout1, $all[$layout1->getId()]);
        $this->assertSame($layout2, $all[$layout2->getId()]);
        $this->assertSame($layout3, $all[$layout3->getId()]);
        $this->assertSame($layout4, $all[$layout4->getId()]);
        $this->assertSame($layout5, $all[$layout5->getId()]);
    }

    /**
     * When you set a token, context must reload layouts from the temp storage
     */
    public function testTransparentTokenLoad()
    {

    }

    /**
     * Test commit and rollback operations
     */
    public function testCommitRollback()
    {
        $context = $this->createContext();
        $storage = $context->getStorage();

        $editableLayout = $storage->create();
        $nonEditableLayout = $storage->create();
        $context->add([$editableLayout], true);
        $context->add([$nonEditableLayout], false);

        // Go to temporary mode and edit the layout
        $token = $context->createEditToken();
        $editableLayout->getTopLevelContainer()->append(new Item('a', 1));
        $nonEditableLayout->getTopLevelContainer()->append(new Item('a', 1));

        $permanentLayout = $storage->load($editableLayout->getId());
        // It is empty, it should be
        $this->assertTrue($permanentLayout->getTopLevelContainer()->isEmpty());
        $this->assertFalse($editableLayout->getTopLevelContainer()->isEmpty());

        // Now, rollback pretty much everything
        $context->rollback();
        $this->assertFalse($context->hasToken());

        $loadedLayout = $storage->load($editableLayout->getId());
        $this->assertTrue($loadedLayout->getTopLevelContainer()->isEmpty());
        // Token should have been deleted
        try {
            $context->setCurrentToken($token->getToken());
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }

        // Start again but we'll commit this time
        $token = $context->createEditToken();
        $newLayout = $context->getAll()[$editableLayout->getId()];
        $this->assertTrue($newLayout->getTopLevelContainer()->isEmpty());

        $newLayout->getTopLevelContainer()->append(new Item('a', 1));
        $this->assertFalse($newLayout->getTopLevelContainer()->isEmpty());

        // Aaaaannd commit!
        $context->commit();
        $this->assertFalse($context->hasToken());

        $loadedOnceAgainLayout = $storage->load($editableLayout->getId());
        $this->assertFalse($loadedOnceAgainLayout->getTopLevelContainer()->isEmpty());
        // Token should have been deleted
        try {
            $context->setCurrentToken($token->getToken());
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Test most error handling operations
     */
    public function testErrorHandler()
    {
        $context = $this->createContext();

        try {
            $context->getCurrentToken();
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        try {
            $context->commit();
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        try {
            $context->rollback();
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        $editToken = $context->createEditToken();
        $this->assertTrue($context->hasToken());
        $this->assertSame($editToken, $context->getCurrentToken());

        try {
            $context->createEditToken();
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        try {
            $context->setCurrentToken('test');
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        $context->commit();
        $this->assertFalse($context->hasToken());

        $context->createEditToken();
        $this->assertTrue($context->hasToken());

        $context->rollback();
        $this->assertFalse($context->hasToken());

        $context->createEditToken();
        $this->assertTrue($context->hasToken());
        $context->resetToken();
        $this->assertFalse($context->hasToken());
    }
}
