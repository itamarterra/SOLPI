import numpy as np

class SOLPIEmbeddings:
    """
    PACOTE 06: EMBEDDINGS v1.0
    Converte IDs de tokens em vetores densos treináveis.
    """
    def __init__(self, vocab_size, embed_dim):
        self.vocab_size = vocab_size
        self.embed_dim = embed_dim
        # Inicialização pequena para facilitar convergência
        self.weights = np.random.randn(vocab_size, embed_dim) * 0.01

    def forward(self, tokens):
        """Busca os vetores correspondentes aos IDs passados."""
        return self.weights[tokens]

    def backward(self, d_out, tokens, lr):
        """Gradiente para atualização futura no loop de treino."""
        for i, token_id in enumerate(tokens):
            self.weights[token_id] -= lr * d_out[i]
