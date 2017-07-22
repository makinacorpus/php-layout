<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Error\InvalidTokenError;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestLayout;

/**
 * Basic context and token testing
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    use ComparisonTestTrait;

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
     * Tests add layout, and ensure layout editable flag
     */
    public function testLayoutAddAndEditable()
    {
        $context = $this->createContext();
        $layoutStorage = $context->getLayoutStorage();

        // Just for the sake of rising the coverage
        $this->assertTrue($context->isEmpty());
        $this->assertFalse($context->hasToken());

        // Now the real tests shall begin
        $layouts = [];
        $layouts[1] = $layout1 = $layoutStorage->create();
        $layouts[2] = $layout2 = $layoutStorage->create();
        $layouts[3] = $layout3 = $layoutStorage->create();
        $layouts[4] = $layout4 = $layoutStorage->create();

        $context->addLayoutList([$layout1->getId(), $layout2->getId(), $layout4->getId()]);
        $context->addLayout($layout3->getId());

        $this->assertFalse($context->isEmpty());
        foreach ($layouts as $layout) {
            $this->assertFalse($context->isEditable($layout));
        }

        $loaded = $context->getAllLayouts();
        foreach ($layouts as $layout) {
            $this->assertArrayHasKey($layout->getId(), $loaded);
        }

        foreach ($layouts as $layout) {
            $this->assertSame($layout->getId(), $context->getLayout($layout->getId())->getId());
        }
    }

    /**
     * When creating a token, you may either edit them all, or specify a list of layout identifiers
     */
    public function testTokenBehaviors()
    {
        $context = $this->createContext();
        $storage = $context->getLayoutStorage();

        $layoutId1 = $storage->create()->getId();
        $layoutId2 = $storage->create()->getId();
        $layoutId3 = $storage->create()->getId();

        $context->addLayoutList([$layoutId1, $layoutId2, $layoutId3]);

        // Do not specify: edit them all
        $token = $context->createEditToken([]);
        $this->assertFalse($token->contains($layoutId1));
        $this->assertFalse($token->contains($layoutId2));
        $this->assertFalse($token->contains($layoutId3));

        // Specify a list of layouts to edit
        $context->rollback();
        $token = $context->createEditToken([$layoutId3]);
        $this->assertFalse($token->contains($layoutId1));
        $this->assertFalse($token->contains($layoutId2));
        $this->assertTrue($token->contains($layoutId3));

        // Specify a list of layouts to edit (another)
        $context->rollback();
        $token = $context->createEditToken([$layoutId1, $layoutId2]);
        $this->assertTrue($token->contains($layoutId1));
        $this->assertTrue($token->contains($layoutId2));
        $this->assertFalse($token->contains($layoutId3));
    }

    /**
     * Test commit and rollback operations
     */
    public function testCommitRollback()
    {
        $context = $this->createContext();
        $storage = $context->getLayoutStorage();

        $editableLayout = $storage->create();
        $editableId = $editableLayout->getId();
        $nonEditableLayout = $storage->create();
        $nonEditableId = $nonEditableLayout->getId();

        $context->addLayoutList([$editableId, $nonEditableId]);

        // Go to temporary mode and edit the layout
        $token = $context->createEditToken([$editableId]);
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
            $context->setToken($token->getToken());
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }

        // Start again but we'll commit this time
        $token = $context->createEditToken([$editableId]);
        $newLayout = $context->getAllLayouts()[$editableId];
        $this->assertTrue($newLayout->getTopLevelContainer()->isEmpty());

        $newLayout->getTopLevelContainer()->append(new Item('a', 1));
        $this->assertFalse($newLayout->getTopLevelContainer()->isEmpty());

        // Aaaaannd commit!
        $context->commit();
        $this->assertFalse($context->hasToken());

        $loadedOnceAgainLayout = $storage->load($editableId);
        $this->assertFalse($loadedOnceAgainLayout->getTopLevelContainer()->isEmpty());
        // Token should have been deleted
        try {
            $context->setToken($token->getToken());
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
            $context->getToken();
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

        $editToken = $context->createEditToken([]);
        $this->assertTrue($context->hasToken());
        $this->assertSame($editToken, $context->getToken());

        try {
            $context->createEditToken([]);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        try {
            $context->setToken('test');
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        $context->commit();
        $this->assertFalse($context->hasToken());

        $context->createEditToken([]);
        $this->assertTrue($context->hasToken());

        $context->rollback();
        $this->assertFalse($context->hasToken());

        $context->createEditToken([]);
        $this->assertTrue($context->hasToken());
        $context->resetToken();
        $this->assertFalse($context->hasToken());
    }
}
