#include <stdio.h>
#include "cScale/src/scale.h"
#include "../src/scale_ffi.h"
#include <assert.h>
#include <string.h>


int main()
{
    assert(strcasecmp(compact_u32_encode(1), "04") == 0);
    assert(compact_u32_decode("04")== 1);

    assert(strcasecmp(option_bool_encode("None"), "00") == 0);
    assert(strcasecmp(option_bool_decode("01"), "true") == 0);

    assert(strcasecmp(bool_encode(true), "01") == 0);
    assert(bool_decode("01") == true);

    struct ResultsType resultTest;
    resultTest.ok= 2;
    resultTest.err= "";
    assert(strcasecmp(results_encode(&resultTest), "0002000000") == 0);

    struct ResultsType resultDecodeRaw = *results_decode("0002000000");
    assert(resultDecodeRaw.ok==2);

    assert(strcasecmp(string_encode("Hamlet"), "1848616d6c6574") == 0);

    uint32_t values[6] = { 1,2,3,4,5,6 };
    assert(strcasecmp(fixU32_encode(values,6), "010000000200000003000000040000000500000006000000") == 0);

    assert(strcasecmp(vec_u32_encode(values,6), "18010000000200000003000000040000000500000006000000") == 0);

    unsigned int *fixU32Ptr = fixU32_decode("010000000200000003000000040000000500000006000000");
    for ( int i = 0; i < 6; i++ ) {
        assert(values[i]==*(fixU32Ptr + i));
    }

}