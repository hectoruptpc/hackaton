#include <stdio.h>
#include <string.h>

// La bandera real para la aplicación web es: FLAG{REVERSE_IS_FUN}
// La contraseña secreta que deben encontrar para ver el mensaje es "UniHacks2025".

int main() {
    char password[20];
    const char correct_password[] = "UniHacks2025";

    printf("Bienvenido al desafío de Ingeniería Inversa.\n");
    printf("Introduce la contraseña secreta para obtener la bandera: ");

    // Leer la entrada del usuario
    if (fgets(password, sizeof(password), stdin) == NULL) {
        return 1;
    }

    // Eliminar el salto de línea que añade fgets
    password[strcspn(password, "\n")] = 0;

    // Comparar la entrada con la contraseña correcta
    if (strcmp(password, correct_password) == 0) {
        printf("\n¡Contraseña correcta!\n");
        printf("Tu bandera es: FLAG{REVERSE_IS_FUN}\n");
    } else {
        printf("\nContraseña incorrecta. Inténtalo de nuevo.\n");
    }

    return 0;
}

/* INSTRUCCIONES PARA EL ADMINISTRADOR:
 1 .* Compila este archivo: gcc reverse_challenge.c -o reverse_challenge
 2. Comprime el binario: zip reverse_challenge.zip reverse_challenge
 3. Sube el .zip al servidor (donde lo enlaza index.php)
 */
