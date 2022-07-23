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


    public function OptionBoolEncode():string
    {
        $o = $this->FFIInstant->option_bool_encode("None");
        return FFI::string($o);
    }

    public function OptionBoolDecode():string
    {
        $o = $this->FFIInstant->option_bool_decode("01");
        return FFI::string($o);
    }


    public function BoolDecode():bool
    {
        return $this->FFIInstant->bool_decode("01");
    }

    public function BoolEncode():string
    {
        $o = $this->FFIInstant->bool_encode(true);
        return FFI::string($o);
    }


}

