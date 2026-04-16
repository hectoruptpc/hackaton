# Código de referencia para generar el texto cifrado (Vigenère)
def vigenere_encrypt(plaintext, key):
    key = key.upper()
    ciphertext = ''
    key_index = 0
    for char in plaintext:
        char_code = ord(char.upper())
        if 65 <= char_code <= 90:  # Es una letra mayúscula (A-Z)
            shift = ord(key[key_index % len(key)]) - 65
            encrypted_char = chr(((char_code - 65 + shift) % 26) + 65)

            # Mantener el caso (opcional, simplificado para el ejercicio: todo mayúsculas)
            if 'a' <= char <= 'z':
                ciphertext += encrypted_char.lower()
            else:
                ciphertext += encrypted_char

            key_index += 1
        elif '0' <= char <= '9': # Manejar números si fuera necesario
            ciphertext += char
        else: # Mantener otros caracteres (espacios, etc.)
            ciphertext += char
    return ciphertext

# Ejemplo de uso:
# Clave: ALAN (Alan Turing)
# Mensaje: LA BANDERA ES EL GENIO ALAN
# Texto en claro simplificado: LABANDERAE SELGENIOALAN
# Resultado del cifrado: VQJ WPGS QD YJG JCEMS

# NOTA: En el ejemplo de la página web se usa el texto: Vqj wpgs qd yjg jcems
# La bandera a ingresar es: FLAG{EL_GENIO_ALAN}
