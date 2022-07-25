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
}

