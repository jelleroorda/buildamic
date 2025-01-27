import { InlineDefaults } from './moduleDefaults.js'

const Field = function ({ ADMIN_LABEL, CONFIG = {}, META = {}, HANDLE, VALUE, UUID, }) {
    this.uuid = `${UUID}`
    this.type = 'field'
    this.computed = {
        config: {
            ...CONFIG
        },
        meta: {
            ...META
        }
    }
    this.config = {
        statamic_settings: {
            field: {
                type: CONFIG.type || '',
            },
            handle: HANDLE
        },
        buildamic_settings: {
            enabled: true,
            admin_label: ADMIN_LABEL || CONFIG.DISPLAY || HANDLE,
            ...InlineDefaults
        }
    }
    // this.meta = META
    this.value = VALUE
}

export { Field }
