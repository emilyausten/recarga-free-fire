export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(200).json({ ok: true, message: 'Use POST para notificações' });
  }

  try {
    // Apenas registra o corpo para confirmação
    console.log('Webhook SyncPay:', req.body);
    return res.status(200).json({ ok: true });
  } catch (e) {
    return res.status(500).json({ ok: false, error: String(e) });
  }
}
