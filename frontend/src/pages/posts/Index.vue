<template>
  <q-page padding>
    <v-page-header titulo="Posts" subtitulo="CRUD com WebSocket em tempo real" :breadcrumbs="[
      { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
      { label: 'Posts' },
    ]">
      <template #acoes>
        <q-btn unelevated color="primary" label="Novo Post" icon="fa-light fa-plus" size="sm"
          @click="modalAberto = true" />
      </template>
    </v-page-header>

    <!-- Indicador de post recebido via WebSocket -->
    <q-banner v-if="novoPostWs" class="bg-green-1 text-green-9 q-mb-md" rounded>
      <template #avatar>
        <q-icon name="fa-light fa-bolt" color="green" />
      </template>
      Novo post recebido em tempo real: <strong>{{ novoPostWs.title }}</strong>
      <template #action>
        <q-btn flat label="Fechar" color="green" @click="novoPostWs = null" />
      </template>
    </q-banner>

    <!-- Lista de Posts -->
    <div v-if="loading" class="row justify-center q-pa-xl">
      <q-spinner-dots size="40px" color="primary" />
    </div>

    <div v-else-if="posts.length === 0" class="text-center q-pa-xl">
      <v-empty-state
        icon="fa-light fa-newspaper"
        titulo="Nenhum post ainda"
        descricao="Crie o primeiro post para testar o WebSocket."
      />
    </div>

    <div v-else class="row q-col-gutter-md">
      <div v-for="post in posts" :key="post.id" class="col-md-4 col-sm-6 col-xs-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle1 text-weight-bold">{{ post.title }}</div>
            <div class="text-caption text-grey q-mb-sm">
              {{ formatDate(post.created_at) }}
            </div>
            <div class="text-body2">{{ post.body }}</div>
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat size="sm" color="primary" icon="fa-light fa-pen" label="Editar"
              @click="editarPost(post)" />
            <q-btn flat size="sm" color="negative" icon="fa-light fa-trash" label="Excluir"
              @click="confirmarExclusao(post)" />
          </q-card-actions>
        </q-card>
      </div>
    </div>

    <!-- Modal Criar/Editar -->
    <modal v-model="modalAberto" :titulo="postEditando ? 'Editar Post' : 'Novo Post'" tamanho="sm"
      @close="fecharModal">
      <div class="row q-col-gutter-md">
        <div class="col-12">
          <v-label label="Título" obrigatorio />
          <q-input v-model="form.title" outlined dense placeholder="Título do post" />
        </div>
        <div class="col-12">
          <v-label label="Conteúdo" obrigatorio />
          <q-input v-model="form.body" outlined dense type="textarea" rows="4"
            placeholder="Escreva o conteúdo..." />
        </div>
      </div>
      <template #rodape>
        <q-btn outline color="grey-7" label="Cancelar" @click="fecharModal" />
        <q-btn unelevated color="primary" :label="postEditando ? 'Salvar' : 'Criar'"
          :loading="salvando" @click="salvarPost" />
      </template>
    </modal>

    <!-- Confirmar exclusão -->
    <v-confirm v-model="confirmExcluir" tipo="danger" titulo="Excluir post?"
      :mensagem="`Tem certeza que deseja excluir '${postExcluindo?.title}'?`"
      label-confirmar="Excluir" :loading="excluindo" @confirm="excluirPost" @cancel="confirmExcluir = false" />
  </q-page>
</template>

<script setup>
import { ref, onMounted, onUnmounted, getCurrentInstance } from 'vue'
import { api } from 'src/boot/axios'
import { Notify } from 'quasar'

const { proxy } = getCurrentInstance()

const posts = ref([])
const loading = ref(true)
const salvando = ref(false)
const excluindo = ref(false)
const modalAberto = ref(false)
const confirmExcluir = ref(false)
const postEditando = ref(null)
const postExcluindo = ref(null)
const novoPostWs = ref(null)

const form = ref({
  title: '',
  body: '',
})

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
}

const carregarPosts = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/api/posts')
    posts.value = data
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Erro ao carregar posts' })
  } finally {
    loading.value = false
  }
}

const salvarPost = async () => {
  salvando.value = true
  try {
    if (postEditando.value) {
      const { data } = await api.put(`/api/posts/${postEditando.value.id}`, form.value)
      const idx = posts.value.findIndex(p => p.id === data.id)
      if (idx !== -1) posts.value.splice(idx, 1, data)
      Notify.create({ type: 'positive', message: 'Post atualizado!' })
    } else {
      const { data } = await api.post('/api/posts', form.value)
      posts.value.unshift(data)
      Notify.create({ type: 'positive', message: 'Post criado!' })
    }
    fecharModal()
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Erro ao salvar post' })
  } finally {
    salvando.value = false
  }
}

const editarPost = (post) => {
  postEditando.value = post
  form.value = { title: post.title, body: post.body }
  modalAberto.value = true
}

const confirmarExclusao = (post) => {
  postExcluindo.value = post
  confirmExcluir.value = true
}

const excluirPost = async () => {
  excluindo.value = true
  try {
    await api.delete(`/api/posts/${postExcluindo.value.id}`)
    posts.value = posts.value.filter(p => p.id !== postExcluindo.value.id)
    Notify.create({ type: 'positive', message: 'Post excluído!' })
    confirmExcluir.value = false
  } catch (e) {
    Notify.create({ type: 'negative', message: 'Erro ao excluir post' })
  } finally {
    excluindo.value = false
  }
}

const fecharModal = () => {
  modalAberto.value = false
  postEditando.value = null
  form.value = { title: '', body: '' }
}

// WebSocket — escuta novos posts em tempo real
let channel = null

onMounted(() => {
  carregarPosts()

  channel = proxy.$echo.channel('posts')
  channel.listen('PostCreated', (e) => {
    // Só adiciona se não for um post que eu mesmo criei
    const exists = posts.value.find(p => p.id === e.post.id)
    if (!exists) {
      posts.value.unshift(e.post)
      novoPostWs.value = e.post
    }
  })
})

onUnmounted(() => {
  if (channel) {
    proxy.$echo.leave('posts')
  }
})
</script>
