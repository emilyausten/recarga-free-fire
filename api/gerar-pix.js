export default async function handler(req, res) {
  // CORS para chamadas do mesmo domínio e desenvolvimento
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
  if (req.method === 'OPTIONS') return res.status(200).end();

  if (req.method !== 'POST') return res.status(405).json({ error: 'Método não permitido' });

  try {
    const { amount, customer } = req.body || {};
    if (!amount || !customer?.name || !customer?.email || !customer?.cpf) {
      return res.status(400).json({ error: 'Dados obrigatórios não fornecidos' });
    }

    // Postback no domínio Vercel informado
    const postbackUrl = 'https://recarga-free-fire.vercel.app/api/webhook';

    const payload = {
      amount,
      customer: {
        name: customer.name,
        email: customer.email,
        cpf: String(customer.cpf).replace(/\D/g, '')
      },
      postbackUrl
    };

    const auth = 'Basic ' + Buffer.from('d0bb944b4e93470dfc084a95:').toString('base64');

    const resp = await fetch('https://api.syncpay.pro/v1/gateway/api/', {
      method: 'POST',
      headers: { Authorization: auth, 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await resp.json().catch(() => ({}));
    return res.status(resp.status).json(data);
  } catch (e) {
    return res.status(500).json({ error: 'Falha ao gerar PIX', details: String(e) });
  }
}
