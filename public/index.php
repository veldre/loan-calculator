<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Loan Calculator</title>

    <!-- Tailwind -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Alpine -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>


<body class="flex items-center justify-center min-h-screen bg-cyan-900">
    <div
        x-data="{
            form: {
                type: 'annuity',
                principal: '',
                months: '',
                apr: ''
            },
            calculate: async function () {

                try {
                    const response = await fetch('calculation.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();

                    console.log('Response from backend:', data);

                } catch (error) {
                    console.error('Request failed:', error);
                }
            }
        }"
        class="w-full max-w-md p-8 bg-white rounded">

        <h1 class="mb-6 text-2xl font-bold text-cyan-900">
            Loan Calculator
        </h1>

        <form @submit.prevent="calculate" class="space-y-4">

            <div>
                <label for="form.type" class="block mb-1 text-sm font-medium">Loan Type</label>
                <select x-model="form.type" class="w-full p-2 border rounded cursor-pointer" id="form.type">
                    <option value="annuity">Annuity</option>
                    <option value="linear">Linear</option>
                </select>
            </div>

            <div>
                <label for="form.principal" class="block mb-1 text-sm font-medium">Loan Amount: €</label>
                <input type="number" min="100" step="100" required x-model="form.principal" class="w-full p-2 border rounded" id="form.principal">
            </div>

            <div>
                <label for="form.months" class="block mb-1 text-sm font-medium">Months</label>
                <input type="number" min="1" required x-model="form.months" class="w-full p-2 border rounded" id="form.months">
            </div>

            <div>
                <label for="form.apr" class="block mb-1 text-sm font-medium">APR (%)</label>
                <input type="number" min="0" required step="0.01" x-model="form.apr" class="w-full p-2 border rounded" id="form.apr">
            </div>

            <button type="submit" class="w-full px-4 py-2 mt-6 text-white rounded cursor-pointer bg-cyan-900 hover:bg-cyan-800">
                Calculate
            </button>
        </form>

    </div>
</body>

</html>