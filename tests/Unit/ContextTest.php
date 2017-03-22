<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Controller\Context;
use MakinaCorpus\Layout\Controller\DefaultTokenGenerator;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestLayout;
use MakinaCorpus\Layout\Controller\EditToken;

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

    public function testEditToken()
    {
        $context = new Context();

        // Just for the sake of rising the coverage
        $tokenGenerator = new DefaultTokenGenerator();
        $this->assertInstanceOf(DefaultTokenGenerator::class, $context->getTokenGenerator());
        $this->assertNotSame($tokenGenerator, $context->getTokenGenerator());
        $context->setTokenGenerator($tokenGenerator);
        $this->assertSame($tokenGenerator, $context->getTokenGenerator());

        // Now the real tests shall begin
        $layouts = [];
        $layouts[1] = $layout1 = new TestLayout(1);
        $layouts[2] = $layout2 = new TestLayout(2);
        $layouts[3] = $layout3 = new TestLayout(3);
        $layouts[4] = $layout4 = new TestLayout(4);
        $layouts[5] = $layout5 = new TestLayout(5);
        $layouts[6] = $layout6 = new TestLayout(6);

        $context->add([$layout1, $layout2], true);
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
    }


}
