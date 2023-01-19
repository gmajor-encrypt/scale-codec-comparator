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
        // reg ffi custom type
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

        $this->assertEquals("0002000000", $this->FFICodec->ResultEncode(2, ""));

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

    /**
     * compact<u32>
     *
     * @return void
     */
    public function testCompactU32 ()
    {
        $this->assertEquals($this->codec->process("Compact<u32>", new ScaleBytes("08")), $this->FFICodec->CompactU32Decode("08"));
        $this->assertEquals($this->codec->process("Compact<u32>", new ScaleBytes("00")), $this->FFICodec->CompactU32Decode("00"));
        $this->assertEquals($this->codec->process("Compact<u32>", new ScaleBytes("02000400")), $this->FFICodec->CompactU32Decode("02000400"));
        $this->assertEquals($this->codec->createTypeByTypeString("Compact<u32>")->encode(2), $this->FFICodec->CompactU32Encode(2));
        $this->assertEquals($this->codec->createTypeByTypeString("Compact<u32>")->encode(0), $this->FFICodec->CompactU32Encode(0));
        $this->assertEquals($this->codec->createTypeByTypeString("Compact<u32>")->encode(65536), $this->FFICodec->CompactU32Encode(65536));
    }

    /**
     * option<bool>
     *
     * @return void
     */
    public function testOptionBool ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("OptionBool")->encode(null), $this->FFICodec->OptionBoolEncode("None"));
        $this->assertEquals($this->codec->createTypeByTypeString("OptionBool")->encode(true), $this->FFICodec->OptionBoolEncode("true"));
        $this->assertEquals($this->codec->createTypeByTypeString("OptionBool")->encode(false), $this->FFICodec->OptionBoolEncode("false"));
        $this->assertEquals($this->codec->process("OptionBool", new ScaleBytes("01")), $this->FFICodec->OptionBoolDecode("01") == "true");
        $this->assertEquals($this->codec->process("OptionBool", new ScaleBytes("02")), $this->FFICodec->OptionBoolDecode("02") == "true");
        $this->assertEquals($this->codec->process("OptionBool", new ScaleBytes("00")), $this->FFICodec->OptionBoolDecode("00") == "None" ? null : true);
    }

    /**
     * bool
     *
     * @return void
     */
    public function testBool ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("bool")->encode(true), $this->FFICodec->BoolEncode(true));
        $this->assertEquals($this->codec->createTypeByTypeString("bool")->encode(false), $this->FFICodec->BoolEncode(false));
        $this->assertEquals($this->codec->process("bool", new ScaleBytes("01")), $this->FFICodec->BoolDecode("01"));
        $this->assertEquals($this->codec->process("bool", new ScaleBytes("00")), $this->FFICodec->BoolDecode("00"));
    }

    /**
     * result<u32,string>
     *
     * @return void
     */
    public function testResultU32String ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("result<u32,string>")->encode(["Ok" => 2]), $this->FFICodec->ResultEncode(2, ""));
        $this->assertEquals($this->codec->createTypeByTypeString("result<u32,string>")->encode(["Err" => "err"]), $this->FFICodec->ResultEncode(0, "err"));
        $this->assertEquals($this->codec->process("result<u32,string>", new ScaleBytes("0002000000"))['Ok'], $this->FFICodec->ResultDecode("0002000000")['OK']);
        $this->assertEquals($this->codec->process("result<u32,string>", new ScaleBytes("010c657272"))['Err'], $this->FFICodec->ResultDecode("010c657272")['ERR']);
    }

    /**
     * string
     *
     * @return void
     */
    public function testString ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("string")->encode("Hamlet"), $this->FFICodec->StringEncode("Hamlet"));
        $this->assertEquals($this->codec->createTypeByTypeString("string")->encode("Война и мир"), $this->FFICodec->StringEncode("Война и мир"));
        $this->assertEquals($this->codec->createTypeByTypeString("string")->encode("三国演义"), $this->FFICodec->StringEncode("三国演义"));
        $this->assertEquals($this->codec->process("string", new ScaleBytes("1848616d6c6574")), $this->FFICodec->StringDecode("1848616d6c6574"));
        $this->assertEquals($this->codec->process("string", new ScaleBytes("30e4b889e59bbde6bc94e4b989")), $this->FFICodec->StringDecode("30e4b889e59bbde6bc94e4b989"));
        $this->assertEquals($this->codec->process("string", new ScaleBytes("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180")), $this->FFICodec->StringDecode("50d092d0bed0b9d0bdd0b020d0b820d0bcd0b8d180"));
    }

    /**
     * struct<u32,u8>
     *
     * @return void
     */
    public function testStruct ()
    {
        $this->assertEquals($this->codec->process("CodecStruct", new ScaleBytes("0a00000001")), $this->FFICodec->StructDecode("0a00000001"));
        $this->assertEquals($this->codec->process("CodecStruct", new ScaleBytes("0a00000002")), $this->FFICodec->StructDecode("0a00000002"));
        $this->assertEquals($this->codec->process("CodecStruct", new ScaleBytes("0000000001")), $this->FFICodec->StructDecode("0000000001"));
        $this->assertEquals($this->codec->createTypeByTypeString("CodecStruct")->encode(["Data" => 10, "Other" => 1]), $this->FFICodec->StructEncode(["Data" => 10, "Other" => 1]));
        $this->assertEquals($this->codec->createTypeByTypeString("CodecStruct")->encode(["Data" => 100, "Other" => 20]), $this->FFICodec->StructEncode(["Data" => 100, "Other" => 20]));
        $this->assertEquals($this->codec->createTypeByTypeString("CodecStruct")->encode(["Data" => 0, "Other" => 0]), $this->FFICodec->StructEncode(["Data" => 0, "Other" => 0]));
    }

    /**
     * enum<u32,u32,u32>
     *
     * @return void
     */
    public function testEnum ()
    {
        $this->assertEquals($this->codec->process("EnumStruct", new ScaleBytes("0001000000"))["a"], $this->FFICodec->EnumDecode("0001000000")["a"]);
        $this->assertEquals($this->codec->process("EnumStruct", new ScaleBytes("0101000000"))["b"], $this->FFICodec->EnumDecode("0101000000")["b"]);
        $this->assertEquals($this->codec->process("EnumStruct", new ScaleBytes("0201000000"))["c"], $this->FFICodec->EnumDecode("0201000000")["c"]);
        $this->assertEquals($this->codec->createTypeByTypeString("EnumStruct")->encode(["a" => 1]), $this->FFICodec->EnumEncode(["a" => 1, "b" => 0, "c" => 0]));
        $this->assertEquals($this->codec->createTypeByTypeString("EnumStruct")->encode(["b" => 100]), $this->FFICodec->EnumEncode(["a" => 0, "b" => 100, "c" => 0]));
        $this->assertEquals($this->codec->createTypeByTypeString("EnumStruct")->encode(["c" => 300]), $this->FFICodec->EnumEncode(["a" => 0, "b" => 0, "c" => 300]));
    }

    /**
     * tuple(u32,u32)
     *
     * @return void
     */
    public function testTuple ()
    {
        $this->assertEquals(array_values($this->codec->process("(u32,u32)", new ScaleBytes("0a00000001000000"))), array_values($this->FFICodec->TupleDecode("0a00000001000000")));
        $this->assertEquals(array_values($this->codec->process("(u32,u32)", new ScaleBytes("0a00000005000000"))), array_values($this->FFICodec->TupleDecode("0a00000005000000")));
        $this->assertEquals($this->codec->createTypeByTypeString("(u32,u32)")->encode([10, 1]), $this->FFICodec->TupleEncode(["A" => 10, "B" => 1]));
        $this->assertEquals($this->codec->createTypeByTypeString("(u32,u32)")->encode([100000, 100000]), $this->FFICodec->TupleEncode(["A" => 100000, "B" => 100000]));
    }

    /**
     * [u32; 6]
     *
     * @return void
     */
    public function testFixedU32 ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("[u32;6]")->encode([1, 2, 3, 4, 5, 6]), $this->FFICodec->FixU32Encode([1, 2, 3, 4, 5, 6]));
        $this->assertEquals($this->codec->createTypeByTypeString("[u32;6]")->encode([555, 555, 555, 555, 555, 555]), $this->FFICodec->FixU32Encode([555, 555, 555, 555, 555, 555]));
        $this->assertEquals($this->codec->process("[u32;6]", new ScaleBytes("010000000200000003000000040000000500000006000000")), $this->FFICodec->FixU32Decode("010000000200000003000000040000000500000006000000"));
        $this->assertEquals($this->codec->process("[u32;6]", new ScaleBytes("090000000200000004000000050000000500000006000000")), $this->FFICodec->FixU32Decode("090000000200000004000000050000000500000006000000"));
    }

    /**
     * vec<u32>
     *
     * @return void
     */
    public function testVecU32U32 ()
    {
        $this->assertEquals($this->codec->createTypeByTypeString("Vec<u32>")->encode([1, 2, 3, 4, 5, 6]), $this->FFICodec->VecU32Encode([1, 2, 3, 4, 5, 6]));
        $this->assertEquals($this->codec->createTypeByTypeString("Vec<u32>")->encode([3, 2, 1, 4, 5, 6, 7]), $this->FFICodec->VecU32Encode([3, 2, 1, 4, 5, 6, 7]));
        $this->assertEquals($this->codec->process("Vec<u32>", new ScaleBytes("18010000000200000003000000040000000500000006000000")), $this->FFICodec->VecU32Decode("18010000000200000003000000040000000500000006000000"));
        $this->assertEquals($this->codec->process("Vec<u32>", new ScaleBytes("18010000000400000005000000070000000500000008000000")), $this->FFICodec->VecU32Decode("18010000000400000005000000070000000500000008000000"));
    }

}