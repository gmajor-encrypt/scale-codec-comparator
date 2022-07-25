<?php

namespace Comparator;

use FFI;

class CodecFFI
{
    /**
     * @var $FFIInstant
     */
    public $FFIInstant;

    public function __construct ()
    {
        $dirPath = dirname(__FILE__, 4);
        $this->FFIInstant = FFI::cdef(
            file_get_contents($dirPath . "/src/scale_ffi.h"),
            $dirPath . '/lib/libscale_ffi.dylib'
        );
    }

    public function CompactU32Encode (): string
    {
        $o = $this->FFIInstant->compact_u32_encode(2);
        return FFI::string($o);
    }

    public function CompactU32Decode (): int
    {
        return $this->FFIInstant->compact_u32_decode("08");
    }


    public function OptionBoolEncode (): string
    {
        $o = $this->FFIInstant->option_bool_encode("None");
        return FFI::string($o);
    }

    public function OptionBoolDecode (): string
    {
        $o = $this->FFIInstant->option_bool_decode("01");
        return FFI::string($o);
    }


    public function BoolDecode (): bool
    {
        return $this->FFIInstant->bool_decode("01");
    }

    public function BoolEncode (): string
    {
        $o = $this->FFIInstant->bool_encode(true);
        return FFI::string($o);
    }


    public function ResultEncode (): string
    {
        $o = $this->FFIInstant->results_encode(2, "NONE", "OK");
        return FFI::string($o);
    }

    public function ResultDecode (): array
    {
        $o = $this->FFIInstant->results_decode("0002000000");
        $ok = $o->ok;
        $err = $o->err;
        return ["OK" => $ok, "ERR" => FFI::string($err)];
    }

    public function StructEncode (): string
    {
        $tv = $this->FFIInstant->new("struct CodecStruct");
        $tv->data = 10;
        $tv->other = 1;
        $o = $this->FFIInstant->data_struct_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    public function StructDecode (): array
    {
        $o = $this->FFIInstant->data_struct_decode("0a00000001");
        $data = $o->data;
        $other = $o->other;
        return ["Data" => $data, "Other" => $other];
    }

    public function EnumEncode (): string
    {
        $tv = $this->FFIInstant->new("struct EnumStruct");
        $tv->a = 1;
        $o = $this->FFIInstant->data_enum_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    public function EnumDecode (): array
    {
        $o = $this->FFIInstant->data_enum_decode("0001000000");
        return ["a" => $o->a, "b" => $o->b, "c" => $o->c];
    }

    public function StringDecode (string $s): string
    {
        return FFI::string($this->FFIInstant->string_decode($s));
    }

    public function StringEncode (string $s): string
    {
        $o = $this->FFIInstant->string_encode($s);
        return FFI::string($o);
    }


    public function FixU32Encode (array $s): string
    {
        $fixedUInt = FFI::new("unsigned int[6]");
        for ($i = 0; $i < count($s); $i++) {
            $fixedUInt[$i] = $s[$i];
        }
        return FFI::string($this->FFIInstant->fixU32_encode(FFI::addr($fixedUInt[0]), count($s)));
    }

    public function FixU32Decode (string $s): array
    {
        $fixedUInt = FFI::new("unsigned int[6]");
        $o = $this->FFIInstant->fixU32_decode($s);;
        FFI::memcpy($fixedUInt, $o, 6 * 4);
        $r = array();
        foreach ($fixedUInt as $value) {
            $r[] = $value;
        }
        return $r;
    }

    public function VecU32Encode(array $input):string
    {
        $size = count($input);
        $arrayUInt = FFI::new("unsigned int[$size]");
        for ($i = 0; $i < $size; $i++) {
            $arrayUInt[$i] = $input[$i];
        }
        return FFI::string($this->FFIInstant->vec_u32_encode(FFI::addr($arrayUInt[0]), count($input)));
    }

    public function VecU32Decode(string $input):array
    {
        $fixedUInt = FFI::new("unsigned int[6]");
        $o = $this->FFIInstant->vec_u32_decode($input);;
        FFI::memcpy($fixedUInt, $o, 6 * 4);
        $r = array();
        foreach ($fixedUInt as $value) {
            $r[] = $value;
        }
        return $r;
    }


    public function TupleEncode(){
        $tv = $this->FFIInstant->new("struct TupleType");
        $tv->a = 10;
        $tv->b = 1;
        $o = $this->FFIInstant->tuple_u32u32_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    public function TupleDecode(){
        $o = $this->FFIInstant->tuple_u32u32_decode("0a00000001000000");
        $A = $o->a;
        $B = $o->b;
        return ["A" => $A, "B" => $B];
    }




}

