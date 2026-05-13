# Convenções do Projeto

## Formulários

- **Sempre** usar `<v-label>` acima de cada campo de formulário
- Nunca usar a prop `label` diretamente nos q-input/q-select/componentes V
- Campos obrigatórios devem usar a prop `obrigatorio`
- Campos com ajuda contextual devem usar a prop `ajuda`
- Usar layout com `row` + `q-col-gutter-md` e `col-md-*` para organizar campos

```vue
<!-- Correto -->
<div class="row q-col-gutter-md">
  <div class="col-md-6 col-sm-12">
    <v-label label="Nome" obrigatorio />
    <q-input outlined dense />
  </div>
  <div class="col-md-6 col-sm-12">
    <v-label label="CPF" obrigatorio />
    <v-cpf />
  </div>
</div>

<!-- Errado -->
<q-input outlined dense label="Nome" />
<v-cpf label="CPF" />
```

## Ícones

- Usar `fa-light` (FontAwesome Pro Light) como padrão em todo o projeto
- Override global: `font-weight: 400` (mais firme que o padrão 300)
- Nunca usar `fa-duotone`, `fa-solid` ou `fa-regular` — manter consistência com `fa-light`
- Icon set completo mapeado em `src/boot/fontawesome.js`

## Estilo

- Visual shadcn/Tailwind — overrides globais em `src/css/app.scss`
- Paleta Indigo/Moderno em `src/css/quasar.variables.scss`
- Sombras padrão Tailwind (`$box-shadow`, `$box-shadow-sm`, `$box-shadow-lg`)
- Documentação completa dos overrides em `docs/app-scss.md`
- **Nunca usar degradês** (gradients) — sempre cores sólidas via variáveis do tema
- **Sempre usar variáveis CSS** do Quasar: `var(--q-primary)`, `var(--q-positive)`, `var(--q-negative)`, `var(--q-dark)`, etc.
- Estados visuais dos inputs (definidos globalmente em `app.scss`):
  - **Focus**: borda `$primary` + ring sutil `rgba($primary, 0.2)`
  - **Erro**: borda `$negative` + ring `rgba($negative, 0.2)` — sem ícone de exclamação
  - **Válido**: borda `$positive` + background sutil verde + ícone check

## Componentes Globais

Registrados em `src/boot/components.js`, disponíveis sem import:

`modal`, `v-label`, `v-password`, `v-cpf`, `v-cnpj`, `v-telefone`, `v-cep`,
`v-date`, `v-money`, `v-alert`, `v-confirm`, `v-status-badge`, `v-empty-state`,
`v-autocomplete`, `v-select-estado`, `v-select-banco`, `v-otp`, `v-qrcode`, `v-upload`,
`v-painel`, `v-page-header`, `v-stat-card`, `v-avatar`, `v-copy`, `v-timeline`, `v-filter`,
`v-checklist`, `v-select`

## Inputs

- Sempre usar `outlined dense` nos q-input
- Nunca usar `hide-bottom-space` nos componentes V (já está embutido)
- Usar `no-error-icon` para remover o ícone de exclamação em campos com erro — exibir apenas borda vermelha + texto de erro
- Borda de erro é vermelha (`$negative`) globalmente — override em `app.scss` impede herdar a cor `$primary`
- Para validação visual positiva (ex: e-mail válido), usar classe `input-valid` com ícone check no append:
  ```vue
  <q-input :class="{ 'input-valid': emailValido }" no-error-icon>
    <template v-slot:append>
      <q-icon v-if="emailValido" name="fa-light fa-circle-check" class="input-valid-icon" />
    </template>
  </q-input>
  ```
- VTelefone detecta automaticamente fixo (8 dígitos) ou celular (9 dígitos)
- VCep emite evento `@endereco` com dados do ViaCEP
- VDate: prop `picker` abre calendário só pelo ícone; v-model em ISO (YYYY-MM-DD)
- VMoney: v-model emite Number; prefix/suffix estilo Bootstrap input-group
- VSelect: wrapper q-select com `outlined dense`, clearable, emit-value, map-options e checkmark na opção selecionada. Defaults: `optionLabel="nome"`, `optionValue="id"`. Aceita `$attrs` para qualquer prop extra do q-select

## Modal

- Usar o componente `<modal>` (não q-dialog direto)
- Tamanhos: `xs` (400px), `sm` (500px), `md` (600px), `lg` (900px), `xl` (1200px)
- Rodapé via slot `#rodape` — botões ficam alinhados à direita automaticamente
- Fechar via `@close` + setar v-model para false

## Painel Lateral

- Usar `<v-painel>` para side sheets / drawers laterais (não q-dialog direto)
- Props: `titulo`, `subtitulo`, `lado` (`right`/`left`), `largura` (default 480px)
- Rodapé via slot `#rodape` — botões alinhados à direita automaticamente
- Slot `#controles` para botões extras no cabeçalho (antes do X)
- Fechar via `@close` + setar v-model para false
- No mobile (<600px) ocupa 100% da largura automaticamente

```vue
<v-painel v-model="aberto" titulo="Detalhes" lado="right" @close="aberto = false">
  <!-- conteúdo -->
  <template #rodape>
    <q-btn outline label="Cancelar" color="grey-7" @click="aberto = false" />
    <q-btn unelevated label="Salvar" color="primary" />
  </template>
</v-painel>
```

## Confirmação

- Usar `<v-confirm>` para ações destrutivas ou que precisam confirmação
- Tipos: `warning` (amber), `danger` (red), `info` (primary)
- Props: `titulo`, `mensagem`, `labelConfirmar`, `labelCancelar`, `loading`
- Eventos: `@confirm`, `@cancel`

## Utilitários

- Datas: `import { toDisplay, toIso, today } from 'src/utils/date'`
- Nunca criar funções de data avulsas — sempre usar `src/utils/date.js`

## WebSocket (Laravel Echo + Reverb)

### Arquitetura

O Reverb implementa o **protocolo Pusher** localmente. O `pusher-js` é usado apenas como transport layer — a conexão vai direto pro Reverb (seu servidor), **nunca** para o pusher.com.

```
pusher-js (client) → ws://localhost:8085 → Laravel Reverb (local)
```

### Boot (`src/boot/echo.js`)

Registrado no `quasar.config.js` como boot file. Disponibiliza `$echo` globalmente.

```js
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher  // Echo usa pusher-js internamente como transport

const echo = new Echo({
  broadcaster: 'reverb',                              // Protocolo Pusher via Reverb
  key: process.env.REVERB_APP_KEY,                     // Chave pública (mesma do backend)
  wsHost: process.env.REVERB_HOST,                     // localhost (dev) ou dominio (prod)
  wsPort: process.env.REVERB_PORT,                     // 8085
  wssPort: process.env.REVERB_PORT,                    // Mesma porta (quando TLS)
  forceTLS: process.env.REVERB_SCHEME === 'https',     // false em dev, true em prod
  enabledTransports: ['ws', 'wss'],                    // Protocolos habilitados
})
```

### Envs necessárias (`.env` ou `quasar.config.js > build.env`)

```env
REVERB_APP_KEY=<chave do .env backend>
REVERB_HOST=localhost          # Em prod: seu-dominio.com.br
REVERB_PORT=8085               # Em prod atrás de Nginx: 443
REVERB_SCHEME=http             # Em prod: https
```

### Escutando canais em páginas

```vue
<script setup>
import { getCurrentInstance, onMounted, onUnmounted } from 'vue'

const { proxy } = getCurrentInstance()

// Canal público
onMounted(() => {
  proxy.$echo.channel('meu-canal')
    .listen('MeuEvento', (e) => {
      console.log(e.model)
    })
})

// Canal privado (requer autenticação Sanctum)
onMounted(() => {
  proxy.$echo.private('chat.1')
    .listen('NovaMensagem', (e) => {
      mensagens.value.push(e.mensagem)
    })
})

// Sempre limpar ao sair da página
onUnmounted(() => {
  proxy.$echo.leave('meu-canal')
})
</script>
```

### Tipos de canal

| Tipo | Backend | Frontend | Uso |
|---|---|---|---|
| Público | `new Channel('nome')` | `proxy.$echo.channel('nome')` | Dados abertos (feed, notificações globais) |
| Privado | `new PrivateChannel('nome')` | `proxy.$echo.private('nome')` | Dados do usuário (chat, pedidos) |
| Presence | `new PresenceChannel('nome')` | `proxy.$echo.join('nome')` | Saber quem está online (chat, colaboração) |

Canais privados e presence precisam de autorização em `routes/channels.php` no backend.

### Dependências

```bash
npm install laravel-echo pusher-js
```

## Autenticação

- Páginas em `src/pages/autenticacao/` (login, esqueci-senha, criar-conta)
- Layout AuthLayout para todas as páginas de auth
- Rotas: `login`, `esqueci-senha`, `criar-conta`
