import { InlineDefaults } from './moduleDefaults.js'

const Row = function ({ UUID, ADMIN_LABEL }) {
    this.uuid = `${UUID}`
    this.type = 'row'
    this.config = {
        enabled: true,
        buildamic_settings: {
            admin_label: ADMIN_LABEL || this.type,
            ...InlineDefaults
        }
    }
    this.value = []
}

export { Row }
