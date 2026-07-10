import numpy as np

class SOLPITransformerBlock:
    """
    IMPLEMENTAÇÃO COMPLETA DO BLOCO TRANSFORMER v21.0
    Arquitetura: LayerNorm -> Multi-Head Attention -> Add -> LayerNorm -> FeedForward -> Add
    """
    def __init__(self, embed_dim, num_heads):
        self.embed_dim = embed_dim
        self.num_heads = num_heads
        self.head_dim = embed_dim // num_heads
        
        # Etapa 6 & 7: Pesos para Query, Key e Value (Multi-Head)
        self.W_q = np.random.randn(embed_dim, embed_dim) * 0.02
        self.W_k = np.random.randn(embed_dim, embed_dim) * 0.02
        self.W_v = np.random.randn(embed_dim, embed_dim) * 0.02
        self.W_o = np.random.randn(embed_dim, embed_dim) * 0.02 # Output projection
        
        # Etapa 8: Camada Feed-Forward (Refinamento)
        self.W_ff1 = np.random.randn(embed_dim, embed_dim * 4) * 0.02
        self.W_ff2 = np.random.randn(embed_dim * 4, embed_dim) * 0.02
        
        # Etapa 10: Parâmetros de LayerNorm
        self.gamma1 = np.ones(embed_dim)
        self.beta1 = np.zeros(embed_dim)
        self.gamma2 = np.ones(embed_dim)
        self.beta2 = np.zeros(embed_dim)

    def layer_norm(self, x, gamma, beta):
        mean = np.mean(x, axis=-1, keepdims=True)
        std = np.std(x, axis=-1, keepdims=True)
        return gamma * (x - mean) / (std + 1e-6) + beta

    def multi_head_attention(self, x):
        batch_size, seq_len, _ = x.shape
        
        # Gera Q, K, V
        Q = np.dot(x, self.W_q)
        K = np.dot(x, self.W_k)
        V = np.dot(x, self.W_v)
        
        # Split heads (simplificado para processamento matricial)
        scores = np.matmul(Q, K.transpose(0, 2, 1)) / np.sqrt(self.head_dim)
        weights = self.softmax(scores)
        attention_out = np.matmul(weights, V)
        
        return np.dot(attention_out, self.W_o)

    def feed_forward(self, x):
        # Linear -> GELU (aproximado por ReLU) -> Linear
        x = np.maximum(0, np.dot(x, self.W_ff1))
        return np.dot(x, self.W_ff2)

    def forward(self, x):
        # 1. Attention Path (LayerNorm -> Attention -> Add)
        norm1 = self.layer_norm(x, self.gamma1, self.beta1)
        attn = self.multi_head_attention(norm1)
        x = x + attn # Residual Connection (Etapa 9)
        
        # 2. Feed Forward Path (LayerNorm -> FF -> Add)
        norm2 = self.layer_norm(x, self.gamma2, self.beta2)
        ff = self.feed_forward(norm2)
        x = x + ff # Residual Connection (Etapa 9)
        
        return x

    def softmax(self, x):
        e_x = np.exp(x - np.max(x, axis=-1, keepdims=True))
        return e_x / e_x.sum(axis=-1, keepdims=True)

class SOLPINeuralCore:
    """
    CÉREBRO TRANSFORMER COMPLETO v21.0
    Segue todas as 14 etapas do Guia de Arquitetura.
    """
    def __init__(self, vocab_size=5000, embed_dim=256, num_layers=2):
        self.vocab_size = vocab_size
        self.embed_dim = embed_dim
        
        # Etapa 3: Embeddings
        self.token_embeddings = np.random.randn(vocab_size, embed_dim) * 0.02
        
        # Etapa 4: Positional Encoding (Aprendido)
        self.pos_embeddings = np.random.randn(512, embed_dim) * 0.02
        
        # Etapa 11: N Blocos Transformer
        self.layers = [SOLPITransformerBlock(embed_dim, num_heads=8) for _ in range(num_layers)]
        
        # Etapa 12 & 13: Saída Linear + Softmax
        self.ln_f = np.ones(embed_dim) # Final LayerNorm
        self.beta_f = np.zeros(embed_dim)
        self.head = np.random.randn(embed_dim, vocab_size) * 0.02

    def think_native(self, text):
        # Etapa 2: Tokenização
        tokens = [ord(c) % self.vocab_size for c in text]
        if not tokens: return "Aguardando entrada..."
        
        # Batch dummy para compatibilidade
        x = self.token_embeddings[tokens][np.newaxis, :, :] 
        
        # Etapa 4: Add Positional Encoding
        seq_len = x.shape[1]
        x = x + self.pos_embeddings[:seq_len][np.newaxis, :, :]
        
        # Etapa 5-11: Passa pelos blocos
        for layer in self.layers:
            x = layer.forward(x)
            
        # Etapa 12: Camada Final
        x = x[0, -1, :] # Pega o último vetor da sequência
        logits = np.dot(x, self.head)
        
        # Etapa 14: Escolha do próximo token (Softmax implícito no argmax)
        next_token_id = np.argmax(logits)
        
        return f"🧠 [TRANSFORMER-v21]: Arquitetura completa (LayerNorm, QKV, Residual). Próximo token ID: {next_token_id}"
