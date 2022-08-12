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

    public function CompactU32Encode (int $value): string
    {
        $o = $this->FFIInstant->compact_u32_encode($value);
        return FFI::string($o);
    }

    public function CompactU32Decode (string $raw): int
    {
        return $this->FFIInstant->compact_u32_decode($raw);
    }


    public function OptionBoolEncode (string $value): string
    {
        $o = $this->FFIInstant->option_bool_encode($value);
        return FFI::string($o);
    }

    public function OptionBoolDecode (string $raw): string
    {
        $o = $this->FFIInstant->option_bool_decode($raw);
        return FFI::string($o);
    }


    public function BoolDecode (string $raw): bool
    {
        return $this->FFIInstant->bool_decode($raw);
    }

    public function BoolEncode (bool $value): string
    {
        $o = $this->FFIInstant->bool_encode($value);
        return FFI::string($o);
    }


    public function ResultEncode ($value): string
    {
        $o = $this->FFIInstant->results_encode($value, "NONE", "OK");
        return FFI::string($o);
    }

    public function ResultDecode (string $raw): array
    {
        $o = $this->FFIInstant->results_decode($raw);
        $ok = $o->ok;
        $err = $o->err;
        return ["OK" => $ok, "ERR" => FFI::string($err)];
    }

    public function StructEncode (array $value): string
    {
        $tv = $this->FFIInstant->new("struct CodecStruct");
        $tv->data = $value["Data"];
        $tv->other = $value["Other"];
        $o = $this->FFIInstant->data_struct_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    public function StructDecode (string $raw): array
    {
        $o = $this->FFIInstant->data_struct_decode($raw);
        $data = $o->data;
        $other = $o->other;
        return ["Data" => $data, "Other" => $other];
    }

    public function EnumEncode (array $value): string
    {
        $tv = $this->FFIInstant->new("struct EnumStruct");
        $tv->a = $value["a"];
        $tv->b = $value["b"];
        $tv->c = $value["c"];
        $o = $this->FFIInstant->data_enum_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    public function EnumDecode (string $raw): array
    {
        $o = $this->FFIInstant->data_enum_decode($raw);
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

    public function VecU32Encode (array $input): string
    {
        $size = count($input);
        $arrayUInt = FFI::new("unsigned int[$size]");
        for ($i = 0; $i < $size; $i++) {
            $arrayUInt[$i] = $input[$i];
        }
        return FFI::string($this->FFIInstant->vec_u32_encode(FFI::addr($arrayUInt[0]), count($input)));
    }

    public function VecU32Decode (string $input): array
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


    public function TupleEncode (array $value)
    {
        $tv = $this->FFIInstant->new("struct TupleType");
        $tv->a = $value["A"];
        $tv->b = $value["B"];
        $o = $this->FFIInstant->tuple_u32u32_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    public function TupleDecode (string $raw)
    {
        $o = $this->FFIInstant->tuple_u32u32_decode($raw);
        $A = $o->a;
        $B = $o->b;
        return ["A" => $A, "B" => $B];
    }
}

