const { makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const qrcode = require('qrcode-terminal');
const axios = require('axios');

async function iniciarBot() {
    const { state, saveCreds } = await useMultiFileAuthState('sessao_whatsapp');
    
    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: false
    });

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;
        
        if (qr) {
            console.clear();
            console.log('▼ ESCANEIE O QR CODE ABAIXO COM O SEU WHATSAPP ▼\n');
            qrcode.generate(qr, { small: true });
        }
        
        if (connection === 'close') {
            const deveriaReiniciar = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
            if (deveriaReiniciar) iniciarBot();
        } else if (connection === 'open') {
            console.log('\n🎉 CONECTADO AO WHATSAPP COM SUCESSO! O BOT ESTÁ PRONTO.');
        }
    });

    sock.ev.on('creds.update', saveCreds);

    // OUVIR MENSAGENS DO WHATSAPP
    sock.ev.on('messages.upsert', async (m) => {
        if (m.type !== 'notify') return;
        
        const msg = m.messages[0];
        // Evita que o robô responda a si mesmo ou mensagens de grupos
        if (!msg.message || msg.key.fromMe || msg.key.remoteJid.endsWith('@g.us')) return;

        const jidCliente = msg.key.remoteJid;
        const telefone = jidCliente.split('@')[0];
        const textoCliente = msg.message.conversation || msg.message.extendedTextMessage?.text;

        if (!textoCliente) return;

        console.log(`📩 Cliente [${telefone}] disse: ${textoCliente}`);

        try {
            // Envia a mensagem para o seu arquivo PHP rodando no XAMPP
            const respostaPHP = await axios.get(`http://localhost/salgados/webhook.php`, {
                params: {
                    telefone: telefone,
                    mensagem: textoCliente
                }
            });

            // Pega o texto puro retornado pelo PHP
            const textoResposta = respostaPHP.data.trim();

            if (textoResposta) {
                // MANDA A RESPOSTA DE VOLTA PARA O CELULAR DO CLIENTE!
                await sock.sendMessage(jidCliente, { text: textoResposta });
                console.log(`🤖 Bot respondeu para [${telefone}]`);
            }

        } catch (erro) {
            console.error('❌ Erro ao integrar com o PHP:', erro.message);
        }
    });
}

iniciarBot();