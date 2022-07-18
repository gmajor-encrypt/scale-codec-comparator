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

}

