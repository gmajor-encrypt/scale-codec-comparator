<?php

namespace Comparator;

error_reporting(E_WARNING); // ignore codec warning

use FFI;

class CodecFFI
{
    /**
     * @var $FFIInstant
     */
    public $FFIInstant;

    /**
     * load ffi instance
     */
    public function __construct ()
    {
        $dirPath = dirname(__FILE__, 4);
        $this->FFIInstant = FFI::cdef(
            file_get_contents($dirPath . "/src/scale_ffi.h"),
            $dirPath . sprintf('/lib/libscale_ffi.%s', PHP_OS == "Darwin" ? "dylib" : "so")
        );
    }

    /**
     * CompactU32Encode
     * compact<u32> encode use ffi
     *
     * @param int $value
     * @return string
     */
    public function CompactU32Encode (int $value): string
    {
        $o = $this->FFIInstant->compact_u32_encode($value);
        return FFI::string($o);
    }

    /**
     * compact<u32> decode use ffi
     *
     * @param string $raw
     * @return int
     */
    public function CompactU32Decode (string $raw): int
    {
        return $this->FFIInstant->compact_u32_decode($raw);
    }

    /**
     * option<bool> encode use ffi
     *
     * @param string $value
     * @return string
     */
    public function OptionBoolEncode (string $value): string
    {
        $o = $this->FFIInstant->option_bool_encode($value);
        return FFI::string($o);
    }

    /**
     * option<bool> decode use ffi
     *
     * @param string $raw
     * @return string
     */
    public function OptionBoolDecode (string $raw): string
    {
        $o = $this->FFIInstant->option_bool_decode($raw);
        return FFI::string($o);
    }

    /**
     * bool decode use ffi
     *
     * @param string $raw
     * @return bool
     */
    public function BoolDecode (string $raw): bool
    {
        return $this->FFIInstant->bool_decode($raw);
    }

    /**
     * bool encode use ffi
     *
     * @param bool $value
     * @return string
     */
    public function BoolEncode (bool $value): string
    {
        $o = $this->FFIInstant->bool_encode($value);
        return FFI::string($o);
    }

    /**
     * result encode use ffi
     *
     * @param int $value
     * @param string $err
     * @return string
     */
    public function ResultEncode (int $value, string $err): string
    {
        $tv = $this->FFIInstant->new("struct ResultsType");
        $tv->ok = $value;
        $size = strlen($err);
        $cStr = FFI::new("char[$size+1]", 0);
        if ($size > 0) {
            FFI::memcpy($cStr, $err, $size);
        }
        $tv->err = $cStr;
        $o = $this->FFIInstant->results_encode(FFI::addr($tv));
        FFI::free($tv->err);
        return FFI::string($o);
    }

    /**
     * result decode use ffi
     *
     * @param string $raw
     * @return array
     */
    public function ResultDecode (string $raw): array
    {
        $o = $this->FFIInstant->results_decode($raw);
        $ok = $o->ok;
        $err = $o->err;
        return ["OK" => $ok, "ERR" => FFI::string($err)];
    }


    /**
     * struct encode use ffi
     *
     * @param array $value
     * @return string
     */
    public function StructEncode (array $value): string
    {
        $tv = $this->FFIInstant->new("struct CodecStruct");
        $tv->data = $value["Data"];
        $tv->other = $value["Other"];
        $o = $this->FFIInstant->data_struct_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    /**
     * struct encode use ffi
     *
     * @param string $raw
     * @return array
     */
    public function StructDecode (string $raw): array
    {
        $o = $this->FFIInstant->data_struct_decode($raw);
        $data = $o->data;
        $other = $o->other;
        return ["Data" => $data, "Other" => $other];
    }

    /**
     * enum encode use ffi
     *
     * @param array $value
     * @return string
     */
    public function EnumEncode (array $value): string
    {
        $tv = $this->FFIInstant->new("struct EnumStruct");
        $tv->a = $value["a"];
        $tv->b = $value["b"];
        $tv->c = $value["c"];
        $o = $this->FFIInstant->data_enum_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    /**
     * enum decode use ffi
     *
     * @param string $raw
     * @return array
     */
    public function EnumDecode (string $raw): array
    {
        $o = $this->FFIInstant->data_enum_decode($raw);
        return ["a" => $o->a, "b" => $o->b, "c" => $o->c];
    }

    /**
     * string decode use ffi
     *
     * @param string $s
     * @return string
     */
    public function StringDecode (string $s): string
    {
        return FFI::string($this->FFIInstant->string_decode($s));
    }

    /**
     * string encode use ffi
     *
     * @param string $s
     * @return string
     */
    public function StringEncode (string $s): string
    {
        $o = $this->FFIInstant->string_encode($s);
        return FFI::string($o);
    }

    /**
     * [u32; 6] encode use ffi
     *
     * @param array $s
     * @return string
     */
    public function FixU32Encode (array $s): string
    {
        $size = count($s);
        $fixedUInt = FFI::new("unsigned int[$size]");
        for ($i = 0; $i < count($s); $i++) {
            $fixedUInt[$i] = $s[$i];
        }
        return FFI::string($this->FFIInstant->fixU32_encode(FFI::addr($fixedUInt[0]), count($s)));
    }

    /**
     *  [u32; 6] decode use ffi
     *
     * @param string $s
     * @return array
     */
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

    /**
     *  vec<u32> encode use ffi
     *
     * @param array $input
     * @return string
     */
    public function VecU32Encode (array $input): string
    {
        $size = count($input);
        $arrayUInt = FFI::new("unsigned int[$size]");
        for ($i = 0; $i < $size; $i++) {
            $arrayUInt[$i] = $input[$i];
        }
        return FFI::string($this->FFIInstant->vec_u32_encode(FFI::addr($arrayUInt[0]), count($input)));
    }

    /**
     * vec<u32> decode use ffi
     *
     * @param string $input
     * @return array
     */
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

    /**
     * (u32,u32) encode use ffi
     *
     * @param array $value
     * @return mixed
     */
    public function TupleEncode (array $value)
    {
        $tv = $this->FFIInstant->new("struct TupleType");
        $tv->a = $value["A"];
        $tv->b = $value["B"];
        $o = $this->FFIInstant->tuple_u32u32_encode(FFI::addr($tv));
        return FFI::string($o);
    }

    /**
     * (u32,u32) decode use ffi
     *
     * @param string $raw
     * @return array
     */
    public function TupleDecode (string $raw)
    {
        $o = $this->FFIInstant->tuple_u32u32_decode($raw);
        $A = $o->a;
        $B = $o->b;
        return ["A" => $A, "B" => $B];
    }
}

