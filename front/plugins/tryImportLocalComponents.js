import fs from 'fs'
import path from 'path'

function tryImportLocalComponents() {
  return {
    name: 'vite-plugin-vue-local-components-import',
    enforce: 'pre',
    resolveId(source, importer) {
      if (source.includes('vue')) {
        // Check if the local version exists
        const localPath = path.resolve(importer, source.replace('.vue', '.local.vue'));
        if (fs.existsSync(localPath)) {
          return localPath;
        }
      }
    }
  }
}

export default tryImportLocalComponents
