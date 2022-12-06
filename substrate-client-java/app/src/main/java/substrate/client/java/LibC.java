package substrate.client.java;

public interface LibC { // A representation of libC in Java
    int puts(String s); // mapping of the puts function, in C `int puts(const char *s);`
}
