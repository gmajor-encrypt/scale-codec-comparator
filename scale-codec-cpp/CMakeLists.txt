cmake_minimum_required(VERSION 3.10.2)

include ( "cmake/HunterGate.cmake" )

HunterGate(
        URL https://github.com/soramitsu/soramitsu-hunter/archive/tags/v0.23.257-soramitsu17.tar.gz
        SHA1 c7ccd337314b27485b75d0f0f5d5b42e7e3c2629
)

project(scaleCodecCpp)

add_subdirectory(scale-codec-cpp)
message(${CMAKE_CURRENT_LIST_DIR})
add_executable(${PROJECT_NAME} main.cpp)
#find_package(scale REQUIRED)
target_link_libraries(${PROJECT_NAME} scale-codec-cpp)

file(GLOB LIBRARIES "lib/libscale_ffi.dylib")
message("LIBRARIES = ${LIBRARIES}")
target_link_libraries(${PROJECT_NAME} ${LIBRARIES})