export const cardanoPressGovernanceMessages = window.cardanoPressGovernanceMessages || {
    voting: '',
    invalid: '',
}

const transformToAda = (proposalId: string, optionValue: string) => {
    // ADA Amount = 1.xxxxyy
    // xxxx = proposalId
    // yy   = optionValue
    return (1 + ((parseFloat(proposalId) * 100) + parseFloat(optionValue)) / 1000000).toFixed(6)
}

export const handleVote = async (proposalId: string, optionValue: string) => {
    if ('0' === proposalId) {
        return {
            success: false,
            data: cardanoPressGovernanceMessages.invalid,
        }
    }

    const verification = await verifyVote(proposalId, optionValue)

    if (!verification.success) {
        return verification
    }

    const result = await pushTransaction(proposalId, optionValue, verification.data)

    if (result.success) {
        return await pushToDB(proposalId, optionValue, result.data.transaction)
    }

    return result
}

const verifyVote = async (proposalId: string, optionValue: string) => {
    return await fetch(window.cardanoPress.ajaxUrl, {
        method: 'POST',
        body: new URLSearchParams({
            _wpnonce: window.cardanoPress._nonce,
            action: 'cp-governance_proposal_vote_verify',
            proposalId,
            optionValue,
        }),
    }).then((response) => response.json())
}

type VotingData = {
    votingFee: {
        amount: string
        address: {
            mainnet: string
            testnet: string
        }
    }
}

const pushTransaction = async (proposalId: string, optionValue: string, votingData: VotingData) => {
    const adaAmount = transformToAda(proposalId, optionValue)

    try {
        const amount = window.cardanoPress.api.adaToLovelace(adaAmount)
        const Wallet = await window.cardanoPress.api.getConnectedWallet()
        const address = await Wallet.getChangeAddress()

        votingData.votingFee.amount = window.cardanoPress.api.adaToLovelace(votingData.votingFee.amount)

        return await window.cardanoPress.wallet.multisendTx(
            [{ address, amount }, votingData.votingFee].filter((output) => output.amount && output.address)
        )
    } catch (error) {
        return {
            success: false,
            data: error,
        }
    }
}

const pushToDB = async (proposalId: string, optionValue: string, transaction: string) => {
    return await fetch(window.cardanoPress.ajaxUrl, {
        method: 'POST',
        body: new URLSearchParams({
            _wpnonce: window.cardanoPress._nonce,
            action: 'cp-governance_proposal_vote_complete',
            proposalId,
            optionValue,
            transaction,
        }),
    }).then((response) => response.json())
}
