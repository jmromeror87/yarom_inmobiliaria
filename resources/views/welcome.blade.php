

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviarrendar | Inmobiliaria 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .text-gradient { background: linear-gradient(90deg, #E11D48, #2563EB); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <nav :class="scrolled ? 'glass py-3' : 'bg-transparent py-6'" class="fixed w-full z-50 transition-all duration-300 px-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">S</div>
                
            </div>
            
            <div class="hidden md:flex gap-8 font-medium text-sm uppercase tracking-widest">
                <a href="#" class="hover:text-red-500 transition">Inicio</a>
                <a href="#propiedades" class="hover:text-red-500 transition">Propiedades</a>
                <a href="#" class="hover:text-red-500 transition">Consignar</a>
                <a href="#" class="hover:text-red-500 transition">Contacto</a>
            </div>

           <a href="/admin/login" class="inline-block">
    <button class="bg-gradient-to-r from-[#ef4444] to-[#3b82f6] text-white px-8 py-3 rounded-full text-sm font-black uppercase tracking-[0.3em] transition-all hover:scale-105 shadow-xl hover:shadow-[0_0_35px_rgba(239,68,68,0.3)] duration-300">
        Ingresar !
    </button>
</a>
        </div>
    </nav>

    <section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        <div class="absolute top-0 right-0 -z-10 w-1/2 h-full bg-blue-50 rounded-bl-[200px]"></div>
        <div class="absolute -top-20 -left-20 w-64 h-64 bg-red-100 rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
            <div x-init="setTimeout(() => $el.classList.add('translate-y-0', 'opacity-100'), 100)" class="transform translate-y-10 opacity-0 transition duration-1000">
                <span class="inline-block px-4 py-1 bg-red-100 text-red-600 rounded-full text-xs font-bold uppercase tracking-widest mb-4">Inmobiliaria Líder en Ocaña</span>
                <h1 class="text-6xl md:text-7xl font-extrabold leading-tight mb-6">
                    Encuentra tu lugar <br> <span class="text-gradient">ideal hoy.</span>
                </h1>
                <p class="text-lg text-slate-600 mb-8 max-w-md">
                    Más de 25 años transformando el mercado inmobiliario con transparencia, tecnología y seguridad.
                </p>
                
                <div class="glass p-2 rounded-2xl shadow-2xl flex flex-col md:flex-row gap-2">
                    <select class="bg-transparent px-4 py-3 outline-none font-semibold">
                        <option>Arriendo</option>
                        <option>Venta</option>
                    </select>
                    <div class="h-10 w-[1px] bg-slate-200 hidden md:block self-center"></div>
                    <input type="text" placeholder="¿Qué buscas? (Casa, Apto...)" class="flex-grow bg-transparent px-4 py-3 outline-none">
                    <button class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                        Buscar
                    </button>
                </div>
            </div>

            <div class="relative">
                <div class="rounded-3xl overflow-hidden shadow-2xl rotate-2 hover:rotate-0 transition duration-500">
                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80" alt="Hogar Moderno" class="w-full h-[500px] object-cover">
                </div>
                <div class="absolute -bottom-6 -left-6 glass p-6 rounded-2xl shadow-xl">
                    <p class="text-3xl font-bold text-red-600">+500</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Inmuebles Disponibles</p>
                </div>
            </div>
        </div>
    </section>

    <section id="propiedades" class="py-24 max-w-7xl mx-auto px-6" x-data="{ activeTab: 'todos' }">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-4xl font-extrabold mb-2">Destacados</h2>
                <p class="text-slate-500">Nuestra selección exclusiva para ti.</p>
            </div>
            <div class="flex gap-4 text-sm font-bold">
                <button @click="activeTab = 'todos'" :class="activeTab === 'todos' ? 'text-red-600 border-b-2 border-red-600' : 'text-slate-400'" class="pb-2 transition">Todos</button>
                <button @click="activeTab = 'casas'" :class="activeTab === 'casas' ? 'text-red-600 border-b-2 border-red-600' : 'text-slate-400'" class="pb-2 transition">Casas</button>
                <button @click="activeTab = 'apartamentos'" :class="activeTab === 'apartamentos' ? 'text-red-600 border-b-2 border-red-600' : 'text-slate-400'" class="pb-2 transition">Apartamentos</button>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <template x-if="activeTab === 'todos' || activeTab === 'casas'">
                <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=600&q=80" class="group-hover:scale-110 transition duration-700 h-64 w-full object-cover">
                        <div class="absolute top-4 left-4 bg-white/90 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tighter ">Venta</div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 group-hover:text-blue-600 transition">Casa Quinta Los Álamos</h3>
                        <p class="text-slate-500 text-sm mb-4">📍 Ocaña, Sector Norte</p>
                        <div class="flex justify-between items-center pt-4 border-t">
                            <span class="text-2xl font-black text-slate-900">$450M</span>
                            <a href="#" class="text-blue-600 font-bold text-sm">Ver detalles →</a>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="activeTab === 'todos' || activeTab === 'apartamentos'">
                <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500">
                    <div class="relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=600&q=80" class="group-hover:scale-110 transition duration-700 h-64 w-full object-cover">
                        <div class="absolute top-4 left-4 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-bold uppercase ">Arriendo</div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 group-hover:text-blue-600 transition">Apartamento SkyView</h3>
                        <p class="text-slate-500 text-sm mb-4">📍 Centro, Ocaña</p>
                        <div class="flex justify-between items-center pt-4 border-t">
                            <span class="text-2xl font-black text-slate-900">$1.2M <small class="text-xs font-normal">/mes</small></span>
                            <a href="#" class="text-blue-600 font-bold text-sm">Ver detalles →</a>
                        </div>
                    </div>
                </div>
            </template>

            <div class="group bg-slate-900 rounded-3xl p-8 flex flex-col justify-center items-center text-center text-white">
                <h3 class="text-2xl font-bold mb-4">¿Quieres vender o arrendar tu propiedad?</h3>
                <p class="text-slate-400 text-sm mb-8">Déjanos los detalles y nosotros nos encargamos del resto con respaldo legal garantizado.</p>
                <button class="bg-red-600 hover:bg-red-700 w-full py-4 rounded-2xl font-bold transition shadow-xl shadow-red-900/20">
                    Consignar Ahora
                </button>
             </div>
        </div>
    </section>


<footer class="bg-[#0A192F] text-slate-300 pt-20 pb-10 border-t border-[#112240]">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
        
        <div class="space-y-6">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">S</div>
                <span class="font-extrabold text-xl tracking-tight uppercase text-white">Servi<span class="text-blue-400">arrendar</span></span>
            </div>
            <p class="text-sm leading-relaxed text-slate-400">
                Líderes en soluciones inmobiliarias en Ocaña y la región. Más de 25 años garantizando seguridad y confianza en cada contrato.
            </p>
            <div class="flex gap-4">
                <a href="#" class="w-10 h-10 rounded-xl bg-[#112240] border border-[#1d3557] flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all duration-300 shadow-lg group">
                    <span class="text-xs font-bold">FB</span>
                </a>
                <a href="#" class="w-10 h-10 rounded-xl bg-[#112240] border border-[#1d3557] flex items-center justify-center hover:bg-pink-600 hover:text-white transition-all duration-300 shadow-lg group">
                    <span class="text-xs font-bold">IG</span>
                </a>
                <a href="#" class="w-10 h-10 rounded-xl bg-[#112240] border border-[#1d3557] flex items-center justify-center hover:bg-green-600 hover:text-white transition-all duration-300 shadow-lg group">
                    <span class="text-xs font-bold">WA</span>
                </a>
            </div>
        </div>

        <div>
            <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs">Explorar</h4>
            <ul class="space-y-4 text-sm font-medium">
                <li><a href="#" class="hover:text-red-400 transition-colors flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-red-500 opacity-0 group-hover:opacity-100 transition-all"></span>Catálogo de Arriendos</a></li>
                <li><a href="#" class="hover:text-red-400 transition-colors flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-red-500 opacity-0 group-hover:opacity-100 transition-all"></span>Venta de Propiedades</a></li>
                <li><a href="#" class="hover:text-red-400 transition-colors flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-red-500 opacity-0 group-hover:opacity-100 transition-all"></span>Consignar Inmueble</a></li>
                <li><a href="#" class="hover:text-red-400 transition-colors flex items-center gap-2 group"><span class="w-1.5 h-1.5 rounded-full bg-red-500 opacity-0 group-hover:opacity-100 transition-all"></span>Avalúos Urbanos</a></li>
            </ul>
        </div>

        <div>
            <h4 class="text-white font-bold mb-6 uppercase tracking-widest text-xs">Transparencia</h4>
            <ul class="space-y-4 text-sm">
                <li><a href="#" class="hover:text-blue-300 transition-colors">Política de Privacidad</a></li>
                <li><a href="#" class="hover:text-blue-300 transition-colors">Términos y Condiciones</a></li>
                <li><a href="#" class="hover:text-blue-300 transition-colors">Preguntas Frecuentes</a></li>
                <li><a href="#" class="hover:text-blue-300 transition-colors">Descarga de Formularios</a></li>
            </ul>
        </div>

        <div class="bg-[#112240] p-6 rounded-2xl border border-[#1d3557]">
            <h4 class="text-white font-bold mb-4 uppercase tracking-widest text-xs">Ubicación Ocaña</h4>
            <p class="text-sm text-slate-400 mb-4">
                Cra 13 Nro 11-15 Of. 103<br>
                Edificio Banco de Bogotá
            </p>
            <div class="space-y-2 text-sm">
                <p class="flex items-center gap-2 text-white">
                    <span class="text-blue-400 font-bold">T:</span> (607) 561 0274
                </p>
                <p class="flex items-center gap-2 text-white">
                    <span class="text-red-400 font-bold">W:</span> +57 318 693 4710
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 pt-10 border-t border-[#112240] flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-semibold tracking-tighter uppercase text-slate-500">
        <p>© 2026 SERVIARRENDAR LTDA. NIT: 807.001.350-9</p>
        <p class="flex gap-4">
            <span>Diseño por <span class="text-slate-300">Jhoan Romero </span></span>
            <span class="hidden md:inline">|</span>
            <span>Matrícula Arrendador N° 002</span>
        </p>
    </div>
</footer>
</body>
</html>