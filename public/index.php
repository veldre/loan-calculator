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

<body class="min-h-screen py-12 bg-cyan-900">
    <div x-data="loanApp()" class="flex items-start w-full max-w-6xl gap-8 px-6 mx-auto">

        <!-- Calculator -->
        <div class="w-1/3 p-8 bg-white rounded">

            <h1 class="mb-6 text-2xl font-bold text-cyan-900">
                Loan Calculator
            </h1>

            <form @submit.prevent="calculate" class="space-y-4">

                <div x-show="error" x-text="error" class="p-3 mt-4 text-red-700 bg-red-100 rounded"></div>

                <div>
                    <label for="loanType" class="block mb-1 text-sm font-medium">Loan Type</label>
                    <select x-model="form.type" class="w-full p-2 border rounded cursor-pointer" id="loanType">
                        <option value="annuity">Annuity</option>
                        <option value="linear">Linear</option>
                    </select>
                </div>

                <div>
                    <label for="loanPrincipal" class="block mb-1 text-sm font-medium">Loan Amount: €</label>
                    <input type="number" min="1" required x-model="form.principal" class="w-full p-2 border rounded" id="loanPrincipal">
                </div>

                <div>
                    <label for="loanMonths" class="block mb-1 text-sm font-medium">Months</label>
                    <input type="number" min="1" required x-model="form.months" class="w-full p-2 border rounded" id="loanMonths">
                </div>

                <div>
                    <label for="loanApr" class="block mb-1 text-sm font-medium">APR (%)</label>
                    <input type="number" min="0" required step="0.01" x-model="form.apr" class="w-full p-2 border rounded" id="loanApr">
                </div>

                <button type="submit" class="w-full px-4 py-2 mt-6 text-white rounded cursor-pointer bg-cyan-900 hover:bg-cyan-800">
                    Calculate
                </button>
            </form>
        </div>

        <!-- Results -->
        <template x-if="result">
            <div class="w-2/3 p-6 overflow-x-auto bg-white rounded">

                <!-- Totals -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div x-show="result && form.type === 'annuity'" class="p-4 rounded bg-sky-50">
                        <div class="text-sm text-gray-600">Monthly Payment</div>
                        <div class="text-lg font-bold" x-text="formatNumber(result.monthlyPayment)"></div>
                    </div>

                    <div class="p-4 rounded bg-sky-50">
                        <div class="text-sm text-gray-600">Total Interest</div>
                        <div class="text-lg font-bold" x-text="formatNumber(result.totalInterest)"></div>
                    </div>

                    <div class="p-4 rounded bg-sky-50">
                        <div class="text-sm text-gray-600">Total Repayment</div>
                        <div class="text-lg font-bold" x-text="formatNumber(result.totalRepayment)"></div>
                    </div>
                </div>

                <!-- Schedule table -->
                <h2 class="mb-4 text-xl font-bold text-cyan-900">
                    Amortization Schedule
                </h2>

                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="text-center bg-sky-50">
                            <th class="p-2 border">Month</th>
                            <th class="p-2 border">Payment</th>
                            <th class="p-2 border">Interest</th>
                            <th class="p-2 border">Principal</th>
                            <th class="p-2 border">Balance</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="row in result.schedule" :key="row.month">
                            <tr class="text-center hover:bg-gray-50">
                                <td class="p-2 border" x-text="row.month"></td>
                                <td class="p-2 border" x-text="formatNumber(row.payment)"></td>
                                <td class="p-2 border" x-text="formatNumber(row.interest)"></td>
                                <td class="p-2 border" x-text="formatNumber(row.principal)"></td>
                                <td class="p-2 border" x-text="formatNumber(row.balance)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
        
    </div>

    <script>
        function loanApp() {
            return {
                form: {
                    type: 'annuity',
                    principal: '',
                    months: '',
                    apr: ''
                },
                result: null,
                error: null,

                formatNumber(value) {
                    return Number(value).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'EUR'
                    });
                },

                async calculate() {
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

                        if (data.error) {
                            this.error = data.error;
                            this.result = null;
                        } else {
                            this.result = data;
                            this.error = null;
                        }
                    } catch (error) {
                        this.error = 'Error occured. Please try again.';
                        this.result = null;
                    }
                }
            }
        }
    </script>

</body>

</html>