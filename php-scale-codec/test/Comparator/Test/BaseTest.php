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

        $this->assertEquals("0002000000", $this->FFICodec->ResultEncode());
        $this->assertEquals(["OK" => 2, "ERR" => ""], $this->FFICodec->ResultDecode());


        $this->assertEquals(["Data" => 10, "Other" => 1], $this->FFICodec->StructDecode());
        $this->assertEquals("0a00000001", $this->FFICodec->StructEncode());


        $this->assertEquals(["a" => 1, "b" => 0,"c"=>0], $this->FFICodec->EnumDecode());
        $this->assertEquals("0001000000", $this->FFICodec->EnumEncode());


    }


}