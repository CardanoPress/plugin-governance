export const cardanoPressGovernanceMessages = window.cardanoPressGovernanceMessages || {
    voting: '',
    invalid: '',
}

const transformToAda = (proposalId, optionValue) => {
    proposalId = parseFloat(proposalId) * 100
    optionValue = parseFloat(optionValue)

    // ADA Amount = 1.xxxxyy
    // xxxx = proposalId
    // yy   = optionValue
    return (1 + ((proposalId + optionValue) / 1000000)).toFixed(6)
}

export const handleVote = async (proposalId, optionValue) => {
    if ('0' === proposalId) {
        return {
            success: false,
            data: cardanoPressGovernanceMessages.invalid,
        }
    }

    const result = await pushTransaction(proposalId, optionValue)

    if (result.success) {
        return await pushToDB(proposalId, optionValue, result.data.transaction)
    }

    return result
}

const pushTransaction = async (proposalId, optionValue) => {
    const adaAmount = transformToAda(proposalId, optionValue)

    try {
        const amount = cardanoPress.api.adaToLovelace(adaAmount)
        const Wallet = await cardanoPress.api.getConnectedWallet()
        const address = await Wallet.getChangeAddress()

        return await cardanoPress.wallet.paymentTx(address, amount)
    } catch (error) {
        return {
            success: false,
            data: error,
        }
    }
}

const pushToDB = async (proposalId, option, transaction) => {
    return await fetch(cardanoPress.ajaxUrl, {
        method: 'POST',
        body: new URLSearchParams({
            _wpnonce: cardanoPress._nonce,
            action: 'cp-governance_proposal_vote',
            proposalId,
            option,
            transaction,
        }),
    }).then((response) => response.json())
}
