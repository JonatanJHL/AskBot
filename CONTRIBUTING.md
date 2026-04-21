# Guía de Contribución

¡Gracias por tu interés en contribuir a AskBot! 🎉

Este documento establece las pautas para contribuir al proyecto.

---

## 📋 Tabla de Contenidos

- [Código de Conducta](#código-de-conducta)
- [¿Cómo Contribuir?](#cómo-contribuir)
- [Entorno Configuración del Entorno](#configuración-del-entorno)
- [Estándares de Código](#estándares-de-código)
- [Proceso de Pull Request](#proceso-de-pull-request)
- [Reportar Bugs](#reportar-bugs)
- [Solicitar Funciones](#solicitar-funciones)

---

## Código de Conducta

Al participar en este proyecto, te comprometes a:

- ✅ Ser respetuoso con otros contribuyentes
- ✅ Aceptar críticas constructivas de forma positiva
- ✅ Enfocarte en lo que es mejor para la comunidad
- ✅ Mostrar empatía hacia otros miembros de la comunidad

---

## ¿Cómo Contribuir?

### 🐛 Reportar Bugs

Si encuentras un bug, por favor:

1. Busca en [issues](https://github.com/JonatanJHL/askbot/issues) si ya fue reportado
2. Si no, crea un issue con:
   - Título descriptivo
   - Pasos para reproducir
   - Comportamiento esperado vs real
   - Screenshots si aplica

### 💡 Sugerir Funciones

1. Busca issues relacionados
2. Usa la plantilla de feature request
3. Explica el caso de uso

### 🔧 Contribuir Código

1. Fork el repositorio
2. Clona tu fork localmente
3. Crea una rama para tu cambio

---

## Configuración del Entorno

```bash
# Clonar
git clone https://github.com/JonatanJHL/askbot.git
cd askbot

# Instalar dependencias
composer install

# Configurar
cp config.example.php config.php

# Importar base de datos
mysql -u root -p askbot < core/database.sql

# Ejecutar servidor local
php -S localhost:8000 -t public
```

### Ejecutar Tests

```bash
composer test
```

---

## Estándares de Código

### PHP

- Usa **PSR-12** como guía de estilo
- Nombres en inglés para variables y funciones
- Comenta en español o inglés

### Nomenclatura

```php
// ✅ Correcto
class AskBot { }
function procesarMensaje() { }
const MAX_CONVERSACIONES = 100;

// ❌ Incorrecto
class bot { }
function procesarmensaje() { }
const max = 100;
```

### DocBlocks

```php
/**
 * Procesa un mensaje del usuario y retorna la respuesta
 *
 * @param string $mensaje El mensaje del usuario
 * @param string $session_id ID de la sesión
 * @return array{respuesta: string, tipo: string}
 */
public function procesarMensaje(string $mensaje, string $session_id): array
{
    // código
}
```

### Orden de miembros de clase

1. Constantes
2. Propiedades estáticas
3. Propiedades
4. Constructor
5. Métodos estáticos
6. Métodos públicos
7. Métodos privados/protegidos

---

## Proceso de Pull Request

### Antes de Submitir

1. ✅ Ejecuta `composer cs-fix` o formatea tu código
2. ✅ Asegúrate que los tests pasen
3. ✅ Actualiza documentación si es necesario
4. ✅ Commitea con mensajes claros

### Mensajes de Commit

Usa [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: agregar soporte para WhatsApp
fix: corregir error en validación de SQL
docs: actualizar README con nuevos ejemplos
style: formatear código según PSR-12
refactor: optimizar consulta de tablas
test: agregar tests para AskBot::procesarMensaje
```

### Plantilla de PR

```markdown
## Descripción
Breve descripción del cambio

## Tipo de Cambio
- [ ] Bug fix
- [ ] Nueva función
- [ ] Breaking change
- [ ] Documentación

## Checklist
- [ ] Tests pasan
- [ ] Docs actualizadas
- [ ] Código formateado
```

---

## Reportar Bugs

Usa esta plantilla:

```markdown
**Descripción**
Descripción clara y concisa del bug

**Pasos para Reproducir**
1. Ir a '...'
2. Click en '....'
3. Scroll down to '....'
4. Ver error

**Comportamiento Esperado**
Qué debería pasar

**Comportamiento Real**
Qué realmente pasa

**Screenshots**
Si aplica

**Entorno**
- OS: 
- PHP Version: 
- MySQL Version:
```

---

## Solicitar Funciones

Usa esta plantilla:

```markdown
**Problema/Descripción**
Describe el problema o necesidad

**Solución Propuesta**
Cómo imaginas que debería funcionar

**Alternativas**
Otras soluciones que consideraste

**Contexto Adicional**
Screenshots, ejemplos, etc.
```

---

## 🎯 Prioridades

Las contribuciones serán priorizadas así:

1. **Critical** - Bugs que rompen funcionalidades
2. **High** - Mejoras de seguridad o performance
3. **Medium** - Nuevas funciones solicitadas
4. **Low** - Mejoras menores o cosméticas

---

## Preguntas?

- Abre una discusión en GitHub Discussions
- Email: jonatanhidalgoledesma@gmail.com

¡Gracias por tu tiempo y contribuciones! 🙌
