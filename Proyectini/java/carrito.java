package Proyectini.java;

import java.util.ArrayList;
import java.util.List;

public class carrito {
    private float precioTotal;
    private List<productos> productos;

    public carrito(List<productos> productos, float precioTotal) {
        this.productos = productos;
        this.precioTotal = precioTotal;
    }

    public carrito() {
        this.productos = new ArrayList<>();
        this.precioTotal = 0.0f;
    }

    public void agregarProducto(productos nuevoProducto) {
        if (nuevoProducto == null) {
            throw new IllegalArgumentException("El producto no puede ser null");
        }

        this.productos.add(nuevoProducto);
        if (nuevoProducto.getPrecio() != null) {
            this.precioTotal += nuevoProducto.getPrecio();
        }
    }

    public void agregarProducto(productos nuevoProducto, int cantidad) {
        if (nuevoProducto == null) {
            throw new IllegalArgumentException("El producto no puede ser null");
        }
        if (cantidad <= 0) {
            throw new IllegalArgumentException("La cantidad debe ser mayor a 0");
        }

        for (int i = 0; i < cantidad; i++) {
            this.agregarProducto(nuevoProducto);
        }
    }

    public static carrito agregarCarrito(productos nuevoProducto, int cantidad, carrito viejoCarrito) {
        if (viejoCarrito != null) {
            viejoCarrito.agregarProducto(nuevoProducto, cantidad);
            return viejoCarrito;
        } else {
            carrito nuevoCarrito = new carrito();
            nuevoCarrito.agregarProducto(nuevoProducto, cantidad);
            return nuevoCarrito;
        }
    }

    public float getPrecioTotal() {
        return precioTotal;
    }

    public List<productos> getProductos() {
        return new ArrayList<>(productos);
    }

    public int getCantidadProductos() {
        return productos.size();
    }

    @Override
    public String toString() {
        return "Carrito{" +
                "precioTotal=" + precioTotal +
                ", cantidadProductos=" + productos.size() +
                '}';
    }
}
