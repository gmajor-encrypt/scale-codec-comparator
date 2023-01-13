cmake_minimum_required(VERSION 3.10.2)

project(cScale)

add_library(cScale src/type/boolean.c src/type/compact.c src/type/enumeration.c src/type/fixed_int.c src/type/option.c src/type/string.c src/type/tuple.c src/type/vector.c src/util/hex.c src/scale.c)
