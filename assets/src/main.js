window.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine || {}

    Alpine.data('cardanoPressGovernance', () => ({
        async init() {
            console.log('CardanoPress Governance ready!')
        },
    }))
})
