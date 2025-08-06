<div x-data="chatAssistant()" x-cloak class="fixed bottom-5 right-5 z-50">
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="absolute bottom-[calc(4rem+0.5rem)] right-0 flex items-end space-x-4">
        
        <div @click="robotClicked()">
            <svg width="150" height="230" viewBox="0 0 150 230" class="robot-container cursor-pointer drop-shadow-lg">
                <defs>
                    <linearGradient id="gradienteGris" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#ffffff; stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#e0e0e0; stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="gradienteRopa" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#2c3856; stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#1a2338; stop-opacity:1" />
                    </linearGradient>
                    <filter id="glow">
                        <feGaussianBlur stdDeviation="1.5" result="coloredBlur"/>
                        <feMerge>
                            <feMergeNode in="coloredBlur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
                <ellipse cx="75" cy="220" rx="40" ry="5" fill="#000000" opacity="0.15"/>
                <g class="robot-body">
                    <!-- Cabeza humana -->
                    <circle cx="75" cy="35" r="25" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                    <!-- Orejas -->
                    <ellipse cx="50" cy="35" rx="5" ry="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                    <ellipse cx="100" cy="35" rx="5" ry="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                    <!-- Cabello -->
                    <path d="M60 15 C60 10, 90 10, 90 15 L90 25 C90 25, 60 25, 60 25 Z" fill="#2b2b2b"/>
                    <!-- Ojos -->
                    <ellipse cx="65" cy="35" rx="5" ry="4" fill="#ff9c00" filter="url(#glow)">
                        <animate attributeName="ry" values="4;1;4;4;4;4" dur="4s" repeatCount="indefinite" />
                    </ellipse>
                    <ellipse cx="85" cy="35" rx="5" ry="4" fill="#ff9c00" filter="url(#glow)">
                        <animate attributeName="ry" values="4;4;4;1;4;4" dur="4s" begin="0.3s" repeatCount="indefinite" />
                    </ellipse>
                    <!-- Boca -->
                    <path id="robot-mouth" d="M65 45 Q75 50 85 45" fill="none" stroke="#2b2b2b" stroke-width="2">
                        <animate attributeName="d" values="M65 45 Q75 50 85 45;M65 45 Q75 48 85 45;M65 45 Q75 50 85 45" dur="0.7s" begin="indefinite" keyTimes="0;0.5;1" fill="freeze"/>
                    </path>
                    <!-- Cuello -->
                    <rect x="65" y="60" width="20" height="10" fill="#cccccc"/>
                    <!-- Torso -->
                    <rect x="45" y="70" width="60" height="80" rx="15" fill="url(#gradienteRopa)" stroke="#cccccc" stroke-width="0.5"/>
                    <rect x="55" y="80" width="40" height="30" rx="5" fill="#2b2b2b"/>
                    <image href="{{ Storage::disk('s3')->url('logominmerrbt.png') }}" x="60" y="85" height="20" width="30"/>
                    <!-- Hombros -->
                    <rect x="40" y="70" width="70" height="10" rx="5" fill="url(#gradienteRopa)"/>
                    <!-- Brazo izquierdo (articulado) -->
                    <g id="robot-arm-left" class="robot-arm-swing">
                        <rect x="15" y="80" width="20" height="40" rx="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                        <rect x="15" y="120" width="20" height="40" rx="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                        <g id="left-hand">
                            <rect x="18" y="160" width="4" height="10" rx="2" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                            <rect x="23" y="160" width="4" height="10" rx="2" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                            <rect x="28" y="160" width="4" height="10" rx="2" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                        </g>
                    </g>
                    <!-- Brazo derecho (articulado) -->
                    <g id="robot-arm-right" class="robot-arm-wave">
                        <rect x="115" y="80" width="20" height="40" rx="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                        <rect x="115" y="120" width="20" height="40" rx="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                        <g id="right-hand">
                            <rect x="118" y="160" width="4" height="10" rx="2" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                            <rect x="123" y="160" width="4" height="10" rx="2" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                            <rect x="128" y="160" width="4" height="10" rx="2" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                        </g>
                    </g>
                    <!-- Piernas (con rodillas) -->
                    <g id="robot-leg-left">
                        <rect x="50" y="150" width="20" height="40" rx="8" fill="url(#gradienteRopa)" stroke="#cccccc" stroke-width="0.5"/>
                        <rect x="50" y="190" width="20" height="30" rx="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                    </g>
                    <g id="robot-leg-right">
                        <rect x="80" y="150" width="20" height="40" rx="8" fill="url(#gradienteRopa)" stroke="#cccccc" stroke-width="0.5"/>
                        <rect x="80" y="190" width="20" height="30" rx="8" fill="url(#gradienteGris)" stroke="#cccccc" stroke-width="0.5"/>
                    </g>
                </g>
            </svg>
        </div>

        <div class="w-80 sm:w-96 bg-white rounded-xl shadow-2xl flex flex-col" style="height: 60vh;">
            <div class="bg-[#2c3856] text-white p-4 rounded-t-xl text-center">
                <h3 class="font-bold text-lg">Asistente Virtual con IA</h3>
                <p class="text-xs text-gray-300">Inteligencia Artificial de Control Tower</p>
            </div>
            <div id="chat-body" class="flex-1 p-4 space-y-4 overflow-y-auto">
                <template x-for="message in messages" :key="message.id">
                    <div class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div class="max-w-3/4 p-3 rounded-2xl" :class="message.role === 'user' ? 'bg-[#ff9c00] text-white rounded-br-none' : 'bg-gray-200 text-gray-800 rounded-bl-none'">
                            <p class="text-sm" x-html="message.content"></p>
                            <template x-if="message.whatsapp_link">
                                <a :href="message.whatsapp_link" target="_blank" class="inline-block mt-3 px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-bold hover:bg-green-600">
                                    <i class="fab fa-whatsapp mr-2"></i>Contactar Soporte
                                </a>
                            </template>
                        </div>
                    </div>
                </template>
                <div x-show="isLoading" class="flex justify-start">
                    <div class="p-3 rounded-2xl bg-gray-200 text-gray-400 rounded-bl-none"><span class="text-sm italic">Analizando...</span></div>
                </div>
            </div>
            <div class="p-4 border-t">
                <form @submit.prevent="sendMessage()">
                    <div class="flex items-center">
                        <input x-model="userInput" type="text" placeholder="Escribe tu pregunta..." class="flex-1 border-gray-300 rounded-full focus:ring-[#ff9c00] focus:border-[#ff9c00]" autocomplete="off">
                        <button type="submit" class="ml-3 bg-[#ff9c00] text-white p-3 rounded-full hover:bg-orange-600"><svg class="w-6 h-6 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <button @click="toggleChat()" class="absolute bottom-0 right-0 bg-[#2c3856] text-white w-16 h-16 rounded-full shadow-lg flex items-center justify-center transform hover:scale-110 transition-transform">
        <span x-show="!isOpen" x-transition class="absolute -top-1 -right-1 flex h-6 w-12">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#ff9c00] opacity-75"></span>
            <span class="relative inline-flex items-center justify-center rounded-full h-6 w-12 bg-[#ff9c00] text-white text-xs font-bold">NEW</span>
        </span>
        <svg x-show="!isOpen" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        <svg x-show="isOpen" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>

<style>
    @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-8px); } 100% { transform: translateY(0px); } }
    .robot-body { animation: float 4s ease-in-out infinite; }
    
    @keyframes slow-wave { 0% { transform: rotate(-5deg); } 50% { transform: rotate(10deg); } 100% { transform: rotate(-5deg); } }
    .robot-arm-wave { animation: slow-wave 3s ease-in-out infinite; transform-origin: 125px 85px; }
    
    @keyframes swing { 0% { transform: rotate(5deg); } 50% { transform: rotate(-5deg); } 100% { transform: rotate(5deg); } }
    .robot-arm-swing { animation: swing 3s ease-in-out infinite; transform-origin: 25px 85px; }

    @keyframes quick-wave { 0% { transform: rotate(0deg); } 25% { transform: rotate(45deg); } 75% { transform: rotate(-20deg); } 100% { transform: rotate(0deg); } }
    .robot-arm-wave.react, .robot-arm-swing.react { animation: quick-wave 0.7s ease-in-out; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatAssistant', () => ({
        isOpen: false,
        isLoading: false,
        userInput: '',
        messages: [{
            id: Date.now(),
            role: 'assistant',
            content: `¡Hola, {{ auth()->user() ? auth()->user()->name : 'Usuario' }}! Soy el Asistente de IA. ¡Hazme una pregunta!`
        }],
        
        init() {
            this.startAutoGreet();
        },

        toggleChat() {
            this.isOpen = !this.isOpen;

            if (this.isOpen) {
                fetch('{{ route("assistant.reset") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                });

                this.messages = [{
                    id: Date.now(),
                    role: 'assistant',
                    content: `¡Hola, {{ auth()->user() ? auth()->user()->name : 'Usuario' }}! Soy el Asistente de IA. ¡Hazme una pregunta!`
                }];
            }
        },

        robotClicked() {
            const rightArm = document.getElementById('robot-arm-right');
            const leftArm = document.getElementById('robot-arm-left');
            const mouth = document.getElementById('robot-mouth');
            rightArm.classList.add('react');
            leftArm.classList.add('react');
            mouth.beginElement();
            
            setTimeout(() => {
                rightArm.classList.remove('react');
                leftArm.classList.remove('react');
            }, 700);
        },

        autoGreet() {
            if (this.isOpen) {
                this.robotClicked();
                this.messages.push({
                    id: Date.now(),
                    role: 'assistant',
                    content: '¡Hola, estoy aquí para ayudarte!'
                });
                this.scrollToBottom();
            }
        },

        startAutoGreet() {
            setInterval(() => {
                this.autoGreet();
            }, 8000);
        },

        sendMessage() {
            if (this.userInput.trim() === '') return;
            this.messages.push({ id: Date.now(), role: 'user', content: this.userInput });
            const userMessage = this.userInput;
            this.userInput = '';
            this.isLoading = true;
            this.scrollToBottom();

            fetch('{{ route("assistant.chat") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ message: userMessage })
            })
            .then(response => response.json())
            .then(data => {
                const messageToAdd = { id: Date.now(), role: 'assistant' };
                if (data.error) {
                    messageToAdd.content = data.error;
                } else if (data.type === 'escalation') {
                    messageToAdd.content = data.reply;
                    messageToAdd.whatsapp_link = data.whatsapp_link;
                } else {
                    messageToAdd.content = data.reply;
                }
                this.messages.push(messageToAdd);
            })
            .catch(error => {
                console.error('Error:', error);
                this.messages.push({ id: Date.now(), role: 'assistant', content: 'Lo siento, ocurrió un error. Intenta de nuevo más tarde.' });
            })
            .finally(() => {
                this.isLoading = false;
                this.scrollToBottom();
            });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const chatBody = document.getElementById('chat-body');
                chatBody.scrollTop = chatBody.scrollHeight;
            });
        }
    }));
});
</script>