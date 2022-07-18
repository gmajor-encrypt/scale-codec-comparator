<?php

namespace Comparator\Test;

use PHPUnit\Framework\TestCase;
use Comparator\CodecFFI;

final class BaseTest extends TestCase
{

    protected CodecFFI $FFICodec;

    /**
     * @before
     */
    public function initFFI ()
    {
        $this->FFICodec = new CodecFFI();
    }

    public function testCodecCall ()
    {

    }

}