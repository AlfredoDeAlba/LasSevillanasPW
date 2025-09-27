package Proyectini.java;

public class productos {
    String id;
    String nombre;
    Float precio;
    private String foto;

    public productos(String id, String nombre, Float precio) {
        this.id = id;
        this.nombre = nombre;
        this.precio = precio;
    }

    public productos(String id, String nombre, Float precio, String foto) {
        this.id = id;
        this.nombre = nombre;
        this.precio = precio;
        this.foto = foto;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public Float getPrecio() {
        return precio;
    }

    public void setPrecio(Float precio) {
        this.precio = precio;
    }

    public String getFoto() {
        return foto;
    }

    public void setFoto(String foto) {
        this.foto = foto;
    }
}
