import { ref } from 'vue'

type Validator = (value: string) => string | null

export function useClientValidation(rules: Record<string, Validator[]>) {
    const errors = ref<Record<string, string>>({})

    function validateField(field: string, value: string): string | null {
        for (const rule of (rules[field] ?? [])) {
            const error = rule(String(value ?? ''))
            if (error) { return error }
        }
        return null
    }

    function touchField(field: string, value: string): void {
        const error = validateField(field, value)
        const next = { ...errors.value }
        if (error) {
            next[field] = error
        } else {
            delete next[field]
        }
        errors.value = next
    }

    function validate(data: Record<string, string>): boolean {
        const next: Record<string, string> = {}
        for (const field of Object.keys(rules)) {
            const error = validateField(field, String(data[field] ?? ''))
            if (error) { next[field] = error }
        }
        errors.value = next
        return Object.keys(next).length === 0
    }

    function reset(): void {
        errors.value = {}
    }

    return { errors, validate, touchField, reset }
}
