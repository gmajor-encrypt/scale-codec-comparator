<?php

namespace Comparator\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
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
        $generator = Base::create();
        Base::regCustom($generator, [
            // struct
            "CodecStruct" => ["Data" => "u32", "Other" => "u8"],
            // enum
            "EnumStruct" => ["_enum" => ["a" => "u32", "b" => "u32", "c" => "u32"]],
        ]);
        $this->codec = new ScaleInstance($generator);

    }

    public function testCodecCall ()
    {
        $this->assertEquals("08", $this->FFICodec->CompactU32Encode(2));
        $this->assertEquals(2, $this->FFICodec->CompactU32Decode("08"));

        $this->assertEquals("00", $this->FFICodec->OptionBoolEncode("None"));
        $this->assertEquals("true", $this->FFICodec->OptionBoolDecode("01"));

        $this->assertEquals(true, $this->FFICodec->BoolDecode("01"));
        $this->assertEquals("01", $this->FFICodec->BoolEncode(true));

        $this->assertEquals("0002000000", $this->FFICodec->ResultEncode(2,""));

        $this->assertEquals(["OK" => 2, "ERR" => ""], $this->FFICodec->ResultDecode("0002000000"));
        $this->assertEquals(["OK" => 0, "ERR" => "err"], $this->FFICodec->ResultDecode("010c657272"));


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


    public function testResultU32 ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("result<u32,string>")->encode(["Ok" => 2]), $this->FFICodec->ResultEncode(2,""));
        $this->assertEquals($this->codec->process("result<u32,string>", new ScaleBytes("0002000000"))['Ok'], $this->FFICodec->ResultDecode("0002000000")['OK']);
        $this->assertEquals($this->codec->process("result<u32,string>", new ScaleBytes("010c657272"))['Err'], $this->FFICodec->ResultDecode("010c657272")['ERR']);
    }

    public function testString ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("string")->encode("Hamlet"), $this->FFICodec->StringEncode("Hamlet"));
        $this->assertEquals($this->codec->process("string", new ScaleBytes("1848616d6c6574")), $this->FFICodec->StringDecode("1848616d6c6574"));
    }

    public function testStruct ()
    {
        $this->assertEquals($this->codec->process("CodecStruct", new ScaleBytes("0a00000001")), $this->FFICodec->StructDecode("0a00000001"));
        $this->assertEquals($this->codec->createTypeByTypeString("CodecStruct")->encode(["Data" => 10, "Other" => 1]), $this->FFICodec->StructEncode(["Data" => 10, "Other" => 1]));
    }

    public function testEnum ()
    {
        $this->assertEquals($this->codec->process("EnumStruct", new ScaleBytes("0001000000"))["a"], $this->FFICodec->EnumDecode("0001000000")["a"]);
        $this->assertEquals($this->codec->createTypeByTypeString("EnumStruct")->encode(["a" => 1]), $this->FFICodec->EnumEncode(["a" => 1, "b" => 0, "c" => 0]));
    }

    public function testTuple ()
    {
        $this->assertEquals(array_values($this->codec->process("(u32,u32)", new ScaleBytes("0a00000001000000"))), array_values($this->FFICodec->TupleDecode("0a00000001000000")));
        $this->assertEquals($this->codec->createTypeByTypeString("(u32,u32)")->encode([10, 1]), $this->FFICodec->TupleEncode(["A" => 10, "B" => 1]));
    }

    public function testFixedU32 (){
        $this->assertEquals( $this->codec->createTypeByTypeString("[u32;6]")->encode([1, 2, 3, 4, 5, 6]),$this->FFICodec->FixU32Encode([1, 2, 3, 4, 5, 6]));
        $this->assertEquals($this->codec->process("[u32;6]", new ScaleBytes("010000000200000003000000040000000500000006000000")), $this->FFICodec->FixU32Decode("010000000200000003000000040000000500000006000000"));
    }

    public function testVecU32U32 (){
        $this->assertEquals($this->codec->createTypeByTypeString("Vec<u32>")->encode([1, 2, 3, 4, 5, 6]), $this->FFICodec->VecU32Encode([1, 2, 3, 4, 5, 6]));
        $this->assertEquals($this->codec->process("Vec<u32>", new ScaleBytes("18010000000200000003000000040000000500000006000000")), $this->FFICodec->VecU32Decode("18010000000200000003000000040000000500000006000000"));
    }

}