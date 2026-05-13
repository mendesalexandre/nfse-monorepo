<template>
  <Editor v-model="model" :init="config" />
</template>

<script setup>
import Editor from '@tinymce/tinymce-vue'

defineOptions({ name: 'VEditor' })

// TinyMCE core — importar ANTES dos plugins
import tinymce from 'tinymce/tinymce'
import 'tinymce/models/dom'
import 'tinymce/themes/silver'
import 'tinymce/icons/default'
import 'tinymce/skins/ui/oxide/skin.css'

// Setar license key GPL antes de qualquer plugin
tinymce.overrideDefaults({ license_key: 'gpl', promotion: false })

// Idioma
import 'tinymce-i18n/langs7/pt_BR'

// Plugins
import 'tinymce/plugins/lists/plugin'
import 'tinymce/plugins/link/plugin'
import 'tinymce/plugins/image/plugin'
import 'tinymce/plugins/table/plugin'
import 'tinymce/plugins/code/plugin'
import 'tinymce/plugins/fullscreen/plugin'
import 'tinymce/plugins/preview/plugin'
import 'tinymce/plugins/searchreplace/plugin'
import 'tinymce/plugins/wordcount/plugin'
import 'tinymce/plugins/charmap/plugin'
import 'tinymce/plugins/emoticons/plugin'
import 'tinymce/plugins/emoticons/js/emojis.js'
import 'tinymce/plugins/anchor/plugin'
import 'tinymce/plugins/insertdatetime/plugin'
import 'tinymce/plugins/media/plugin'
import 'tinymce/plugins/nonbreaking/plugin'
import 'tinymce/plugins/pagebreak/plugin'
import 'tinymce/plugins/visualblocks/plugin'
import 'tinymce/plugins/codesample/plugin'
import 'tinymce/plugins/autoresize/plugin'
import 'tinymce/plugins/quickbars/plugin'

const props = defineProps({
  altura: {
    type: Number,
    default: 300,
  },
  idioma: {
    type: String,
    default: 'pt_BR',
  },
  menu: {
    type: Boolean,
    default: false,
  },
  toolbar: {
    type: String,
    default: 'fullscreen bold italic underline forecolor backcolor alignleft aligncenter alignright alignjustify bullist numlist removeformat copy paste table selectall code',
  },
})

const model = defineModel({ default: '' })

const config = {
  license_key: 'gpl',
  plugins: [
    'fullscreen', 'preview', 'searchreplace', 'visualblocks',
    'image', 'link', 'media', 'table', 'charmap', 'pagebreak',
    'nonbreaking', 'anchor', 'insertdatetime', 'lists', 'wordcount',
    'charmap', 'emoticons', 'code', 'codesample', 'quickbars', 'autoresize',
  ],
  toolbar: props.toolbar,
  height: props.altura,
  language: props.idioma,
  skin: false,
  content_css: false,
  content_style: `
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14px;
    }
  `,
  font_family_formats: `
    Arial=arial,helvetica,sans-serif;
    Times New Roman=times new roman,times;
    Courier New=courier new,courier;
    Calibri=calibri;
    Verdana=verdana
  `,
  menubar: props.menu,
  branding: false,
  promotion: false,
}
</script>
