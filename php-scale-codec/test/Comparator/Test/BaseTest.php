<?php

namespace Comparator\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use phpDocumentor\Reflection\Types\Null_;
use PHPUnit\Framework\TestCase;
use Comparator\CodecFFI;

final class BaseTest extends TestCase
{

    protected CodecFFI $FFICodec;

    protected ScaleInstance $codec;

    /**
     * @before
     */
    public function initFFI ()
    {
        $this->FFICodec = new CodecFFI();
        $this->codec = new ScaleInstance(Base::create());
    }

    public function testCodecCall ()
    {
        $this->assertEquals("08", $this->FFICodec->CompactU32Encode(2));
        $this->assertEquals(2, $this->FFICodec->CompactU32Decode("08"));

        $this->assertEquals("00", $this->FFICodec->OptionBoolEncode("None"));
        $this->assertEquals("true", $this->FFICodec->OptionBoolDecode("01"));

        $this->assertEquals(true, $this->FFICodec->BoolDecode("01"));
        $this->assertEquals("01", $this->FFICodec->BoolEncode(true));

        $this->assertEquals("0002000000", $this->FFICodec->ResultEncode(2));
        $this->assertEquals(["OK" => 2, "ERR" => ""], $this->FFICodec->ResultDecode("0002000000"));


        $this->assertEquals(["Data" => 10, "Other" => 1], $this->FFICodec->StructDecode("0a00000001"));
        $this->assertEquals("0a00000001", $this->FFICodec->StructEncode(["Data" => 10, "Other" => 1]));


        $this->assertEquals(["a" => 1, "b" => 0, "c" => 0], $this->FFICodec->EnumDecode("0001000000"));
        $this->assertEquals("0001000000", $this->FFICodec->EnumEncode(["a" => 1, "b" => 0, "c" => 0]));


        $this->assertEquals("1848616d6c6574", $this->FFICodec->StringEncode("Hamlet"));
        $this->assertEquals("Hamlet", $this->FFICodec->StringDecode("1848616d6c6574"));

        $this->assertEquals("010000000200000003000000040000000500000006000000", $this->FFICodec->FixU32Encode([1, 2, 3, 4, 5, 6]));
        $this->assertEquals([1, 2, 3, 4, 5, 6], $this->FFICodec->FixU32Decode("010000000200000003000000040000000500000006000000"));

        $this->assertEquals("18010000000200000003000000040000000500000006000000", $this->FFICodec->VecU32Encode([1, 2, 3, 4, 5, 6]));
        $this->assertEquals([1, 2, 3, 4, 5, 6], $this->FFICodec->VecU32Decode("18010000000200000003000000040000000500000006000000"));


        $this->assertEquals("0a00000001000000", $this->FFICodec->TupleEncode(["A" => 10, "B" => 1]));
        $this->assertEquals(["A" => 10, "B" => 1], $this->FFICodec->TupleDecode("0a00000001000000"));
    }


    public function testCompactU32 ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("Compact<u32>")->encode(2), $this->FFICodec->CompactU32Encode(2));
        $this->assertEquals($this->codec->process("Compact<u32>", new ScaleBytes("08")), $this->FFICodec->CompactU32Decode("08"));
    }


    public function testOptionBool ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("Option<bool>")->encode(null), $this->FFICodec->OptionBoolEncode("None"));
        $this->assertEquals($this->codec->process("Option<bool>", new ScaleBytes("01")), $this->FFICodec->OptionBoolDecode("01") == "true");
    }

    public function testBool ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("bool")->encode(true), $this->FFICodec->BoolEncode(true));
        $this->assertEquals($this->codec->process("bool", new ScaleBytes("01")), $this->FFICodec->BoolDecode("01"));
    }

}