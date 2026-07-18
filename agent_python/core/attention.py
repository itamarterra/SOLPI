import numpy as np

class SOLPISelfAttention:
    """
    PACOTE 08: ATTENTION v1.0
    Implementação real de Q, K, V (Query, Key, Value).
    """
    def __init__(self, embed_dim):
        self.embed_dim = embed_dim
        self.head_dim = embed_dim # Simplificado para single head inicialmente
        
        # Inicialização Xavier/Glorot (Etapa 3)
        self.W_q = np.random.randn(embed_dim, embed_dim) * np.sqrt(1/embed_dim)
        self.W_k = np.random.randn(embed_dim, embed_dim) * np.sqrt(1/embed_dim)
        self.W_v = np.random.randn(embed_dim, embed_dim) * np.sqrt(1/embed_dim)

    def forward(self, x):
        """
        Calcula Attention(Q, K, V) = softmax(QK^T / sqrt(dk)) * V
        """
        # 1. Gerar Projeções (Q, K, V)
        Q = np.dot(x, self.W_q)
        K = np.dot(x, self.W_k)
        V = np.dot(x, self.W_v)

        # 2. Calcular Scores de Atenção
        scores = np.dot(Q, K.T) / np.sqrt(self.head_dim)
        
        # 3. Softmax
        weights = self.softmax(scores)
        
        # 4. Saída Contextualizada
        return np.dot(weights, V)

    def softmax(self, x):
        e_x = np.exp(x - np.max(x, axis=-1, keepdims=True))
        return e_x / e_x.sum(axis=-1, keepdims=True)
