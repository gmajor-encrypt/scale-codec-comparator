{-# LANGUAGE DeriveAnyClass      #-}
{-# LANGUAGE DeriveGeneric       #-}
{-# LANGUAGE OverloadedStrings   #-}
{-# LANGUAGE ScopedTypeVariables #-}


module Main where

import           Control.Monad         (forM_)
import           Data.Bit              (castFromWords8)
import           Data.Bits             (bit)
import           Data.ByteString       (ByteString)
import qualified Data.ByteString       as BS (length, pack, unpack)
import           Data.Int              (Int16, Int32, Int64, Int8)
import qualified Data.Text             as T (pack)
import           Data.Vector.Unboxed   (Vector)
import qualified Data.Vector.Unboxed   as V (fromList)
import           Data.Word             (Word16, Word32, Word64, Word8)
import qualified GHC.Generics          as GHC (Generic)
import           Codec.Scale
import           Test.Hspec
import           Test.Hspec.QuickCheck

data Unit = Unit
    deriving (Eq, Show, GHC.Generic, Generic, Encode, Decode)

data EnumType = A
    | B Word32 Word64
    | C
    { a2 :: Word32
    , b2 :: Word64
    }
    deriving (Eq, Show, GHC.Generic, Generic, Encode, Decode)

main :: IO ()
main = hspec $ do
    describe "Regular types" $ do
        prop "Bool" $ \(v :: Bool) -> decode (encode v :: ByteString) == Right v

--    describe "Test FFI CALL"  $ do


