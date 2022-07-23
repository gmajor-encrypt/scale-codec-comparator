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
        $this->assertEquals("08", $this->FFICodec->CompactU32Encode());
        $this->assertEquals(2, $this->FFICodec->CompactU32Decode());

        $this->assertEquals("00", $this->FFICodec->OptionBoolEncode());
        $this->assertEquals("true", $this->FFICodec->OptionBoolDecode());

        $this->assertEquals(true, $this->FFICodec->BoolDecode());
        $this->assertEquals(01, $this->FFICodec->BoolEncode());
    }

}