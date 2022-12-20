cmake_minimum_required(VERSION 3.10.2)

project(MyProject)
add_subdirectory(cScale)
add_executable(${PROJECT_NAME} mainapp.c)
target_link_libraries(${PROJECT_NAME} cScale)


file(GLOB LIBRARIES "../lib/*.dylib")
message("LIBRARIES = ${LIBRARIES}")
target_link_libraries(${PROJECT_NAME} ${LIBRARIES})