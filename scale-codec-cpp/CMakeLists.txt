cmake_minimum_required(VERSION 3.10.2)

project(scaleCodecCpp)
add_executable(${PROJECT_NAME} main.cpp)

file(GLOB LIBRARIES "lib/libscale_ffi.dylib")
message("LIBRARIES = ${LIBRARIES}")
target_link_libraries(${PROJECT_NAME} ${LIBRARIES})