require "scale"

class ResultsType < FFI::Struct
  layout :ok,  :uint32,
         :err,       :pointer
end

class CodecStruct < FFI::Struct
  layout :data,:uint32,
         :other, :uint8
end

class EnumStruct < FFI::Struct
  layout :a,:uint32,
         :b, :uint32,
         :c, :uint8
end

class TupleType < FFI::Struct
  layout :a,:uint32,
         :b, :uint32
end


module Rust
  extend FFI::Library
  ffi_lib "../lib/libscale_ffi." + FFI::Platform::LIBSUFFIX
  attach_function :compact_u32_encode, %i[uint32], :string
  attach_function :compact_u32_decode, %i[string], :uint32

  attach_function :option_bool_encode, %i[string], :string
  attach_function :option_bool_decode, %i[string], :string

  attach_function :bool_decode, %i[string], :bool
  attach_function :bool_encode, %i[bool], :string

  attach_function :results_encode, [ResultsType.by_ref], :string
  attach_function :results_decode, %i[string], ResultsType.by_ref

  attach_function :data_struct_encode, [CodecStruct.by_ref], :string
  attach_function :data_struct_decode, %i[string], CodecStruct.by_ref

  attach_function :data_enum_encode, [EnumStruct.by_ref], :string
  attach_function :data_enum_decode, %i[string], EnumStruct.by_ref

  attach_function :string_encode, %i[string], :string
  attach_function :string_decode, %i[string], :string

  attach_function :tuple_u32u32_encode, [TupleType.by_ref], :string
  attach_function :tuple_u32u32_decode, %i[string], TupleType.by_ref

  attach_function :fixU32_encode, [:pointer, :size_t], :string
  attach_function :fixU32_decode, %i[string], :pointer

  attach_function :vec_u32_encode, [:pointer, :size_t], :string
  attach_function :vec_u32_decode, %i[string], :pointer


end


RSpec.describe Scale::Types do
  before(:all) {
    Scale::TypeRegistry.instance.load
  }


  it "Base FFI codec output as expect" do
      # compact<u32>
      expect(Rust.compact_u32_encode(2)).to eq("08")
      expect(Rust.compact_u32_decode("08")).to eq(2)

      # option<bool>
      expect(Rust.option_bool_encode("None")).to eq("00")
      expect(Rust.option_bool_decode("01")).to eq("true")

      # bool
      expect(Rust.bool_decode("01")).to eq(true )
      expect(Rust.bool_encode(true)).to eq("01")

      # results
      rt = ResultsType.new
      rt[:ok] = 2
      rt[:err] = FFI::MemoryPointer.from_string("")
      expect(Rust.results_encode(rt)).to eq("0002000000")

      results_output = Rust.results_decode("0002000000")
      expect(:Ok=>results_output[:ok]).to eq({:Ok=>2})


      # struct
      struct_output = Rust.data_struct_decode("0a00000001")
      expect(:Data=>struct_output[:data],:Other=>struct_output[:other]).to eq({:Data=>10,:Other=>1})
      cs = CodecStruct.new
      cs[:data] = 10
      cs[:other] = 1
      expect(Rust.data_struct_encode(cs)).to eq("0a00000001")


      # enum
      enum_output = Rust.data_enum_decode("0001000000")
      expect(:a=>enum_output[:a],:b=>enum_output[:b],:c=>enum_output[:c]).to eq({:a=>1,:b=>0,:c=>0})
      cs = EnumStruct.new
      cs[:a] = 1
      expect(Rust.data_enum_encode(cs)).to eq("0001000000")

      # string
      expect(Rust.string_encode("Hamlet")).to eq("1848616d6c6574")
      expect(Rust.string_decode("1848616d6c6574")).to eq("Hamlet")


      # tuple
      tuple_output = Rust.tuple_u32u32_decode("0a00000001000000")
      expect(:a=>tuple_output[:a],:b=>tuple_output[:b]).to eq({:a=>10,:b=>1})
      cs = TupleType.new
      cs[:a] = 10
      cs[:b] = 1
      expect(Rust.tuple_u32u32_encode(cs)).to eq("0a00000001000000")

      # fixed uint32
      fixed_arr = [1, 2, 3, 4, 5, 6]
      size = fixed_arr.size
      ptr = FFI::MemoryPointer.new :uint32, size
      ptr.put_array_of_uint32 0, fixed_arr
      expect(Rust.fixU32_encode(ptr,size)).to eq("010000000200000003000000040000000500000006000000")

      fix_u32_output =  Rust.fixU32_decode("010000000200000003000000040000000500000006000000")
      result_array = fix_u32_output.read_array_of_uint32(size)
      expect(result_array).to eq( [1, 2, 3, 4, 5, 6])
      # vec<u32>
      vec_u32 = [1, 2, 3, 4, 5, 6]
      size = vec_u32.size
      ptr_vec = FFI::MemoryPointer.new :uint32, size
      ptr_vec.put_array_of_uint32 0, vec_u32
      expect(Rust.vec_u32_encode(ptr_vec,size)).to eq("18010000000200000003000000040000000500000006000000")

      vec_u32_output =  Rust.vec_u32_decode("18010000000200000003000000040000000500000006000000")
      result_array = vec_u32_output.read_array_of_uint32(size)
      expect(result_array).to eq( [1, 2, 3, 4, 5, 6])


  end


  it "FFI codec output compare with scala.rb" do
    # compact<u32>
    expect(Rust.compact_u32_encode(2)).to eq(Scale::Types::Compact.new(2).encode)
    expect(Rust.compact_u32_encode(0)).to eq(Scale::Types::Compact.new(0).encode)
    expect(Rust.compact_u32_encode(65536)).to eq(Scale::Types::Compact.new(65536).encode)
    expect(Rust.compact_u32_decode("08")).to eq(Scale::Types::Compact.decode(Scale::Bytes.new("0x08")).value)
    expect(Rust.compact_u32_decode("00")).to eq(Scale::Types::Compact.decode(Scale::Bytes.new("0x00")).value)
    expect(Rust.compact_u32_decode("02000400")).to eq(Scale::Types::Compact.decode(Scale::Bytes.new("0x02000400")).value)

    # option<bool>
    expect(Rust.option_bool_encode("None")).to eq(Scale::Types::OptionBool.new(nil).encode)
    expect(Rust.option_bool_encode("true")).to eq(Scale::Types::OptionBool.new(true).encode)
    expect(Rust.option_bool_encode("false")).to eq(Scale::Types::OptionBool.new(false ).encode)
    expect(Rust.option_bool_decode("01")).to eq(Scale::Types::OptionBool.decode(Scale::Bytes.new("0x01")).value.to_s)
    expect(Rust.option_bool_decode("00")).to eq(Scale::Types::OptionBool.decode(Scale::Bytes.new("0x00")).value.to_s==""?"None":"true")
    expect(Rust.option_bool_decode("02")).to eq(Scale::Types::OptionBool.decode(Scale::Bytes.new("0x02")).value.to_s)
    #
    # bool
    expect(Rust.bool_decode("01")).to eq(Scale::Types::Bool.decode(Scale::Bytes.new("0x01")).value)
    expect(Rust.bool_decode("00")).to eq(Scale::Types::Bool.decode(Scale::Bytes.new("0x00")).value)
    expect(Rust.bool_encode(true)).to eq(Scale::Types::Bool.new(true).encode)
    expect(Rust.bool_encode(false)).to eq(Scale::Types::Bool.new(false ).encode)
    #

    # struct
    #
    struct_type = { "type" => "struct", "type_mapping" => [["Data", "u32"], ["Other", "U8"]] }
    codec_struct = Scale::Types.get(struct_type)

    struct_output = Rust.data_struct_decode("0a00000001")
    expect({"Data"=>struct_output[:data],"Other"=>struct_output[:other]}).to eq(codec_struct.decode(Scale::Bytes.new("0x0a00000001")).value.map{|k,v| [k, v.value]}.to_h)
    struct_output = Rust.data_struct_decode("0a00000002")
    expect({"Data"=>struct_output[:data],"Other"=>struct_output[:other]}).to eq(codec_struct.decode(Scale::Bytes.new("0x0a00000002")).value.map{|k,v| [k, v.value]}.to_h)
    struct_output = Rust.data_struct_decode("0000000001")
    expect({"Data"=>struct_output[:data],"Other"=>struct_output[:other]}).to eq(codec_struct.decode(Scale::Bytes.new("0x0000000001")).value.map{|k,v| [k, v.value]}.to_h)


    cs = CodecStruct.new
    cs[:data] = 10
    cs[:other] = 1
    expect(Rust.data_struct_encode(cs)).to eq(codec_struct.new({"Data"=>Scale::Types::U32.new(10),"Other"=>Scale::Types::U8.new(1)}).encode)
    cs[:data] = 0
    cs[:other] = 0
    expect(Rust.data_struct_encode(cs)).to eq(codec_struct.new({"Data"=>Scale::Types::U32.new(0),"Other"=>Scale::Types::U8.new(0)}).encode)

    # tuple
    tuple_output = Rust.tuple_u32u32_decode("0a00000001000000")
    expect([tuple_output[:a],tuple_output[:b]]).to eq(Scale::Types.get("(U32, U32)").decode(Scale::Bytes.new("0x0a00000001000000")).value.map{|v| v.value})
    tuple_output = Rust.tuple_u32u32_decode("0a00000005000000")
    expect([tuple_output[:a],tuple_output[:b]]).to eq(Scale::Types.get("(U32, U32)").decode(Scale::Bytes.new("0x0a00000005000000")).value.map{|v| v.value})
    cs = TupleType.new
    cs[:a] = 10
    cs[:b] = 1
    expect(Rust.tuple_u32u32_encode(cs)).to eq(Scale::Types.get("(U32, U32)").new([Scale::Types::U32.new(10),Scale::Types::U32.new(1)]).encode)
    cs[:a] = 100000
    cs[:b] = 100000
    expect(Rust.tuple_u32u32_encode(cs)).to eq(Scale::Types.get("(U32, U32)").new([Scale::Types::U32.new(100000),Scale::Types::U32.new(100000)]).encode)

    # vec<u32>
    result_array = Rust.vec_u32_decode("18010000000200000003000000040000000500000006000000").read_array_of_uint32([1, 2, 3, 4, 5, 6].size)
    expect(result_array).to eq( Scale::Types.get("Vec<U32>").decode(Scale::Bytes.new("0x18010000000200000003000000040000000500000006000000")).value.map{|x|x.value})
    result_array = Rust.vec_u32_decode("18010000000400000005000000070000000500000008000000").read_array_of_uint32([1, 2, 3, 4, 5, 6].size)
    expect(result_array).to eq( Scale::Types.get("Vec<U32>").decode(Scale::Bytes.new("0x18010000000400000005000000070000000500000008000000")).value.map{|x|x.value})

  end
  #
  it "FFI codec output compare with scala.rb, result types should support" do
     Scale::Types.get("Results").nil?
  end


  it "FFI codec output compare with scala.rb, enum types should support" do
    enum_type = { "type" => "enum", "type_mapping" => [["a", "u32"], ["b", "u32"],["c","u32"]] }
    codec_enum = Scale::Types.get(enum_type)
    enum_output = Rust.data_enum_decode("0001000000")
    expect(:a=>enum_output[:a]).to eq(codec_enum.decode(Scale::Bytes.new("0x0001000000")))
    cs = EnumStruct.new
    cs[:a] = 1
    value = codec_enum.new({"a"=>Scale::Types::U32.new(1)})
    expect(Rust.data_enum_encode(cs)).to eq(value.encode)
  end

  it "FFI codec output compare with scala.rb, string types should support" do
    expect(Rust.string_decode("1848616d6c6574")).to eq(Scale::Types::String.decode(Scale::Bytes.new("0x1848616d6c6574")).value)
    expect(Rust.string_encode("Hamlet")).to eq(Scale::Types::String.new("Hamlet").encode)
  end

  it "FFI codec output compare with scala.rb, fixed array types should support" do
    Scale::Types.get("VecU32Length6").nil?
  end

end