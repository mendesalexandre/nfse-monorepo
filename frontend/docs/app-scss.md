# app.scss — Documentação Completa

> Override global do Quasar Framework para visual Bootstrap-like + shadcn.
> Arquivo: `src/css/app.scss` — carregado via `quasar.config.js → css: ["app.scss"]`.

---

## Sumário

1. [Variáveis SCSS](#1-variáveis-scss)
2. [Q-Field (Input / Select / Textarea)](#2-q-field)
3. [Focus / Highlighted](#3-focus--highlighted)
4. [Q-Card](#4-q-card)
5. [Q-Notification](#5-q-notification)
6. [Q-Layout](#6-q-layout)
7. [Q-Dialog](#7-q-dialog)
8. [Q-Chip](#8-q-chip)
9. [Q-Toggle](#9-q-toggle)
10. [Q-Select (Dropdown)](#10-q-select-dropdown)
11. [Q-Checkbox](#11-q-checkbox)
12. [Problemas resolvidos](#12-problemas-resolvidos)

---

## 1. Variáveis SCSS

Todas as cores e valores reutilizáveis ficam no topo do arquivo para facilitar customização.

| Variável | Valor | Uso |
|---|---|---|
| `$generic-border-radius` | `0.25rem` (4px) | Border-radius global — padrão Bootstrap |
| `$border-color` | `#ced4da` | Borda dos inputs — idêntico ao Bootstrap |
| `$text-color` | `#495057` | Cor do texto dos campos |
| `$chip-bg` | `#f1f1f1` | Background dos chips e badges |
| `$disabled-opacity` | `0.5` | Opacidade de elementos desabilitados |
| `$transition-fast` | `0.15s cubic-bezier(0.4, 0, 0.2, 1)` | Transição padrão (toggle, checkbox) |
| `$box-shadow-sm` | `0 0.125rem 0.25rem rgba(0,0,0,.075)` | Sombra leve — cards, badges |
| `$box-shadow` | `0 0.5rem 1rem rgba(0,0,0,.15)` | Sombra padrão — notifications, dropdowns |
| `$box-shadow-lg` | `0 1rem 3rem rgba(0,0,0,.175)` | Sombra grande — modais, popovers |
| `$primary` | Vem de `quasar.variables.scss` | Cor primária do tema |

As variáveis `$box-shadow-*` seguem exatamente os valores do [Bootstrap 4/5](https://getbootstrap.com/docs/5.3/utilities/shadows/).

---

## 2. Q-Field

### Problema original
O Quasar define alturas fixas nos campos:
- `.q-field__control` → `height: 56px`
- `.q-field--dense .q-field__control` → `height: 40px`
- `.q-field--auto-height .q-field__control` → `min-height: 56px`

Isso impede o campo de crescer/diminuir naturalmente pelo conteúdo.

### Solução
Estilo **Bootstrap form-control**: altura ditada por `padding + font-size`, não por `height` fixo.

#### `.q-field__control` (container do campo)

```scss
.q-field .q-field__control,
.q-field--outlined .q-field__control,
.q-field--dense .q-field__control,
.q-field--auto-height .q-field__control,
.q-field--auto-height.q-field--dense .q-field__control {
  height: auto !important;
  min-height: 38px !important;
  padding: 0.375rem 0.75rem !important;  // 6px 12px
  font-size: 1rem !important;            // 16px
  font-weight: 400 !important;
  color: $text-color !important;
  background-color: #fff !important;
  background-clip: padding-box !important;
  border-radius: $generic-border-radius !important;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
```

**Por que tantos seletores?** O Quasar aplica alturas com seletores de especificidade variada (`--dense`, `--auto-height`, `--auto-height.--dense`). Para que nosso override vença em todos os cenários, precisamos cobrir cada combinação com `!important`.

**Por que `min-height: 38px`?** O q-select usa `<span>` ao invés de `<input>`. Sem `min-height`, o conteúdo interno do select não gera altura suficiente e o campo colapsa. O valor 38px é compatível com o `calc(1.5em + .75rem + 2px)` do Bootstrap form-control.

#### `.q-field__marginal` (ícones laterais — append/prepend)

```scss
.q-field .q-field__marginal,
.q-field--dense .q-field__marginal {
  height: auto !important;
  min-height: auto !important;
}
```

Remove a altura fixa dos containers de ícones para que acompanhem o campo.

#### `.q-field__native` e irmãos (o input/texto real)

```scss
.q-field .q-field__native,
.q-field .q-field__prefix,
.q-field .q-field__suffix,
.q-field .q-field__input,
.q-field--outlined .q-field__native,
.q-field--auto-height .q-field__native,
.q-field--auto-height.q-field--dense .q-field__native {
  padding: 0 !important;
  min-height: auto !important;
  height: auto !important;
  font-size: 1rem !important;
  line-height: normal !important;
}
```

**`padding: 0`** — O padding vertical já vem do `__control` (container pai). Sem isso, haveria padding duplo.

**`line-height: normal`** — Crítico! O Quasar define `line-height: 28px` nesses elementos. Isso causava o q-input ficar mais alto que o q-select. Com `normal`, ambos usam a line-height natural do browser (~1.2 × font-size), ficando com a mesma altura.

#### Borda outlined

```scss
.q-field--outlined .q-field__control:before {
  border: 1px solid $border-color !important;
}
```

O Quasar usa `::before` para a borda do campo outlined. Sobrescrevemos com `#ced4da` (Bootstrap).

---

## 3. Focus / Highlighted

### Problema original
O Quasar aplica focus via `::after` com `border: 2px solid currentColor` + `transform: scale3d(1,1,1)`. Isso cria uma borda grossa e agressiva no focus.

### Solução
Estilo **shadcn**: ring fino externo com opacidade baixa.

```scss
// Anula o ::after grosso do Quasar
.q-field--outlined .q-field__control:after {
  border: none !important;
  transform: none !important;
}

// Focus — ring fino
.q-field--outlined.q-field--focused .q-field__control {
  box-shadow: 0 0 0 2px rgba($primary, 0.2) !important;

  &:before {
    border-color: $primary !important;
  }
}

// Highlighted (q-select aberto)
.q-field--outlined.q-field--highlighted .q-field__control {
  box-shadow: 0 0 0 2px rgba($primary, 0.2) !important;

  &:before {
    border-color: $primary !important;
  }
}
```

**`::after { border: none }`** — Anula completamente o mecanismo de focus do Quasar.

**`box-shadow: 0 0 0 2px rgba($primary, 0.2)`** — Ring de 2px com 20% de opacidade da cor primária. Sutil e elegante.

**`border-color: $primary`** — A borda `::before` muda para a cor primária no focus.

**Por que `.q-field--highlighted` separado?** O q-select usa a classe `--highlighted` (não `--focused`) quando o dropdown está aberto. Precisa do mesmo estilo.

---

## 4. Q-Card

```scss
.q-card {
  border-radius: $generic-border-radius !important;
  box-shadow: $box-shadow-sm !important;
  transition: box-shadow 0.2s ease;
}
```

Usa `$box-shadow-sm` (Bootstrap `.shadow-sm`) — sombra sutil para cards em repouso.

**`transition: box-shadow`** — Não usa `transition: all` (que animaria width, height, padding desnecessariamente e causa jank).

---

## 5. Q-Notification

```scss
.q-notification {
  border-radius: $generic-border-radius !important;
  box-shadow: $box-shadow !important;
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.q-notification__badge {
  box-shadow: $box-shadow-sm !important;
}
```

Notificação usa `$box-shadow` (Bootstrap `.shadow`) — sombra média para elementos flutuantes. Badge usa `$box-shadow-sm` (mais sutil).

### Mapa de uso das sombras

| Componente | Variável | Bootstrap equivalente |
|---|---|---|
| `.q-card` | `$box-shadow-sm` | `.shadow-sm` |
| `.q-notification` | `$box-shadow` | `.shadow` |
| `.q-notification__badge` | `$box-shadow-sm` | `.shadow-sm` |
| `.q-toggle__thumb:after` | `0 1px 2px rgba(0,0,0,0.1)` | Customizado (profundidade da bolinha) |
| Focus ring | `0 0 0 2px rgba($primary, 0.2)` | Customizado (shadcn style) |

---

## 6. Q-Layout

```scss
.q-layout-container > div > div {
  max-height: none !important;
  min-height: 0;
}
```

**Atenção:** Seletor frágil que depende da estrutura DOM interna do Quasar. Resolve o problema de scroll duplo em containers. Se o Quasar mudar a estrutura DOM em versões futuras, esse seletor pode quebrar silenciosamente.

---

## 7. Q-Dialog

```scss
.q-dialog__inner > div {
  border-radius: $generic-border-radius !important;
}

.q-dialog__inner--minimized {
  padding: 24px 0 !important;
}
```

O `padding: 24px 0` (apenas vertical) evita barra de rolagem dupla que aparecia com o padding padrão do Quasar.

---

## 8. Q-Chip

```scss
.q-chip {
  padding: 0.5rem 0.5rem;
  border-radius: $generic-border-radius !important;
  background-color: $chip-bg;
}
```

Background cinza claro (`#f1f1f1`) ao invés da cor primária do Quasar.

---

## 9. Q-Toggle

### Problema original
O Quasar renderiza o toggle no estilo Material Design: thumb grande flutuando **por cima** de um track fino, com ripple effect ao clicar/hover.

### Solução
Estilo **shadcn/iOS**: pill switch com thumb embutido.

#### Dimensões

| Elemento | Tamanho | Descrição |
|---|---|---|
| Track | 44 × 24px | Pill arredondado (`border-radius: 9999px`) |
| Thumb | 20 × 20px | Círculo branco dentro do track |
| Espaço | 2px | Gap entre thumb e borda do track |

#### Estados

| Estado | Track | Thumb position |
|---|---|---|
| OFF | `#e4e4e7` (cinza) | `left: 2px` |
| ON (truthy) | `$primary` | `left: 22px` |
| Indeterminado | `#a1a1aa` | `left: 12px` |
| Disabled | Mesmo + `opacity: 0.5` | Mesmo |

#### Remoções do Material Design

- **`q-focus-helper`**: `display: none` — remove o ripple ring
- **`::before` do thumb**: `display: none` — remove o hover circle que aparecia no desktop (`body.desktop .q-toggle:not(.disabled) .q-toggle__thumb:before`)
- **Opacity do track**: `opacity: 1` — o Quasar reduz a opacity do track por padrão

---

## 10. Q-Select (Dropdown)

```scss
.q-select {
  .q-item {
    min-height: 38px !important;
    padding: 0;
    color: inherit;
    transition: color 0.3s, background-color 0.3s;
  }

  .q-field__input--padding {
    padding-left: 0 !important;
  }
}
```

Os items do dropdown usam `min-height: 38px` (mesmo do campo) e transição suave de cor.

---

## 11. Q-Checkbox

### Problema original
O Quasar renderiza o checkbox no estilo Material Design: caixa grande (40px container), ripple ao clicar, hover circle no desktop.

### Solução
Estilo **shadcn**: caixa compacta 16×16px, limpa.

#### Dimensões e estilos

| Elemento | Valor |
|---|---|
| Container (`__inner`) | 16 × 16px |
| Caixa (`__bg`) | 16 × 16px, borda `1.5px solid #d4d4d8`, `border-radius: 3px` |
| Checked/Indeterminate | Background + borda = `$primary`, ícone branco |
| Label | `padding-left: 8px`, `font-size: 14px` |

#### Remoções do Material Design

- **`q-focus-helper`**: `display: none` — remove o ripple ring
- **`::before` do inner**: `display: none` — remove o hover circle do desktop (`body.desktop .q-checkbox:not(.disabled) .q-checkbox__inner:before`)

---

## 12. Problemas resolvidos

### Q-Input maior que Q-Select (ou vice-versa)

**Causa:** O Quasar define `line-height: 28px` em `.q-field__native`. O `<input>` (q-input) ignora line-height por ser um elemento replaced. Mas o `<span>` do q-select herda, ficando com altura diferente.

**Solução:** `line-height: normal !important` em todos os `__native`, `__prefix`, `__suffix`, `__input`. Ambos passam a usar a line-height natural do browser.

### Q-Select colapsando sem min-height

**Causa:** O q-select renderiza com a classe `q-field--auto-height`, que no Quasar define `min-height: 56px`. Ao remover com `min-height: auto`, o conteúdo interno (spans) não gerava altura suficiente.

**Solução:** `min-height: 38px !important` em vez de `auto`. Garante piso mínimo sem fixar a altura.

### Focus grosso do Material Design

**Causa:** O Quasar usa `::after` com `border: 2px solid currentColor` + `transform: scale3d(1,1,1)` para indicar focus. Visualmente pesado.

**Solução:** Anular `::after` completamente e usar `box-shadow: 0 0 0 2px rgba($primary, 0.2)` — ring fino estilo shadcn.

### Seletores precisam cobrir --auto-height

**Causa:** O q-select usa a classe `q-field--auto-height` (o q-input não). Seletores genéricos como `.q-field .q-field__control` não vencem a especificidade de `.q-field--auto-height .q-field__control`.

**Solução:** Incluir explicitamente `.q-field--auto-height .q-field__control` e `.q-field--auto-height.q-field--dense .q-field__control` na lista de seletores.

### `transition: all` no Q-Card

**Causa:** `transition: all 0.2s ease` anima TODAS as propriedades (width, height, padding, margin...), causando jank visual desnecessário.

**Solução:** `transition: box-shadow 0.2s ease` — anima apenas o que muda de fato.

---

## Referência: Bootstrap form-control original

Para comparação, o CSS do Bootstrap 4 `form-control`:

```css
.form-control {
  display: block;
  width: 100%;
  height: calc(1.5em + .75rem + 2px);  /* ~38px */
  padding: .375rem .75rem;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid #ced4da;
  border-radius: .25rem;
  transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}
```

Os valores de `padding`, `font-size`, `color`, `background-color`, `border`, `border-radius` e `transition` foram replicados intencionalmente no `app.scss`.
