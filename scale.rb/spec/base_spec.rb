require "scale"

module Rust
  extend FFI::Library
  ffi_lib "../lib/libscale_ffi." + FFI::Platform::LIBSUFFIX
end


RSpec.shared_examples "collections" do |collection_class|

end

RSpec.describe Array do
  include_examples "collections", Array
end

