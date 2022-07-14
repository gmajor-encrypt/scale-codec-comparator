package scale_go

import (
	"testing"

	"github.com/stretchr/testify/assert"
)

func TestHello(t *testing.T) {
	assert.Equal(t, "08", CompactU32Encode())
	assert.Equal(t, uint(2), CompactU32Decode())
	assert.Equal(t, "00", OptionBoolEncode())
	assert.Equal(t, "true", OptionBoolDecode())
}
