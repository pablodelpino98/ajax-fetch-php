Práctica PHP: Implementación de AJAX (fetch)
:
En esta práctica con PHP se ha creado una página web para votar las películas en cartelera de la semana. Los usuarios registrados pueden votar una vez por película, pudiendo editar su voto en cualquier momento.
Se incorpora AJAX (fetch) para validación de formularios y el registro del voto sin necesidad de recargar la página. Se incopora una base de datos para registrar los usuarios, las películas y los votos de los usuarios.
;
;
Página principal:
  ![image](https://github.com/user-attachments/assets/ca7e935e-8418-400a-b447-3df0095fdebb)
;
;
;
Registro de usuarios:
  ![image](https://github.com/user-attachments/assets/fd4addea-b256-4cf6-8c28-3dedcf8a4abf)
  ;
  Vemos que para cada campo hay validaciones:
  - Usuario: valida si el nombre de usuario ya se encuentra registrado.
  - Correo: valida si el correo ya está en uso por otro usuario.
  - Contraseña: valida si la contraseña tiene un mínimo de 6 caracteres.
  - Confirmar contraseña: valida si los input de contraseña coinciden.
  En caso de que la validación no sea favorable, impide el envío de formulario.
  ;
  Vemos el caso donde la información está validada favorablemente para el registro:
  ![image](https://github.com/user-attachments/assets/f94791e7-d95e-46a8-ab91-21ca5f384488)
  ;
  En este caso, la información se ha validad correctamente y se permite el envío del formulario.
;
;
;
Listado de películas:
  ![image](https://github.com/user-attachments/assets/60698c6f-c057-448e-a61e-130a8f2ba49f)
  ;
  Una vez iniciada la sesión, vemos el listado de películas donde aparece la Puntuación de Usuarios que indica la cantidad de usuarios que han votado y se muestra como si fuera una gráfica de calificación. Este actúa de la siguiente manera:
  Las valoraciones de cada película son la media aritmética de las mismas. Es decir, si un cliente ha valorado con 3/5 y otro con 5/5 la valoración será de 4. Como puntuamos sobre 5 la valoración media máxima será de 5 estrellas. Si la parte decimal de la media de las valoraciones es superior o igual a 0.5 cuenta como media estrella. Es decir, si la media de valoraciones es 3,2/5, aparecerían 3 estrellas. Si la media es de 3,7/5, aparecerían 3,5 estrellas.
  ;
  Vamos a votar las películas y vemos el resultado:
  ![image](https://github.com/user-attachments/assets/3bdf257a-84fe-47cd-8799-5b4fb46b48e5)
  ;
  Si intentamos votar de nuevo, estaríamos actualizando nuestro voto. En ningún caso nos dejaría votar dos veces. Aparecería de esta manwra si el usuario volviese a esta página para cambiar su voto:
  ![image](https://github.com/user-attachments/assets/a7ba582b-7e97-419a-b3a1-ed27c4829064)
