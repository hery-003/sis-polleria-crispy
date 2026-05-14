import { ref } from 'vue'

export function usePOSStore() {
    const audioContext = ref(null)
    const soundEnabled = ref(true)

    const initAudio = () => {
        if (!audioContext.value) {
            audioContext.value = new (window.AudioContext || window.webkitAudioContext)()
        }
    }

    const playClick = () => {
        if (!soundEnabled.value) return
        try {
            initAudio()
            const ctx = audioContext.value
            const oscillator = ctx.createOscillator()
            const gainNode = ctx.createGain()

            oscillator.connect(gainNode)
            gainNode.connect(ctx.destination)

            oscillator.frequency.setValueAtTime(800, ctx.currentTime)
            oscillator.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.1)
            oscillator.type = 'sine'

            gainNode.gain.setValueAtTime(0.1, ctx.currentTime)
            gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1)

            oscillator.start(ctx.currentTime)
            oscillator.stop(ctx.currentTime + 0.1)
        } catch (e) {
            console.warn('Audio not available:', e)
        }
    }

    const playSuccess = () => {
        if (!soundEnabled.value) return
        try {
            initAudio()
            const ctx = audioContext.value
            const notes = [523.25, 659.25, 783.99]

            notes.forEach((freq, i) => {
                const oscillator = ctx.createOscillator()
                const gainNode = ctx.createGain()

                oscillator.connect(gainNode)
                gainNode.connect(ctx.destination)

                oscillator.frequency.value = freq
                oscillator.type = 'sine'

                const startTime = ctx.currentTime + i * 0.1
                gainNode.gain.setValueAtTime(0.08, startTime)
                gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + 0.15)

                oscillator.start(startTime)
                oscillator.stop(startTime + 0.15)
            })
        } catch (e) {
            console.warn('Audio not available:', e)
        }
    }

    const playError = () => {
        if (!soundEnabled.value) return
        try {
            initAudio()
            const ctx = audioContext.value
            const oscillator = ctx.createOscillator()
            const gainNode = ctx.createGain()

            oscillator.connect(gainNode)
            gainNode.connect(ctx.destination)

            oscillator.frequency.setValueAtTime(300, ctx.currentTime)
            oscillator.frequency.exponentialRampToValueAtTime(200, ctx.currentTime + 0.2)
            oscillator.type = 'square'

            gainNode.gain.setValueAtTime(0.05, ctx.currentTime)
            gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2)

            oscillator.start(ctx.currentTime)
            oscillator.stop(ctx.currentTime + 0.2)
        } catch (e) {
            console.warn('Audio not available:', e)
        }
    }

    const toggleSound = () => {
        soundEnabled.value = !soundEnabled.value
        return soundEnabled.value
    }

    return {
        soundEnabled,
        playClick,
        playSuccess,
        playError,
        toggleSound
    }
}
