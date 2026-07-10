import numpy as np

class SOLPIAdamW:
    """
    PACOTE 0902: AdamW Optimizer v1.0
    Algoritmo de otimização de elite com Weight Decay.
    """
    def __init__(self, params, lr=1e-3, betas=(0.9, 0.999), eps=1e-8, weight_decay=0.01):
        self.lr = lr
        self.betas = betas
        self.eps = eps
        self.weight_decay = weight_decay
        self.m = [np.zeros_like(p) for p in params]
        self.v = [np.zeros_like(p) for p in params]
        self.t = 0

    def step(self, params, grads):
        self.t += 1
        for i, (p, g) in enumerate(zip(params, grads)):
            # AdamW: Aplica decaimento de peso primeiro
            p *= (1 - self.lr * self.weight_decay)
            
            # Atualiza momentos
            self.m[i] = self.betas[0] * self.m[i] + (1 - self.betas[0]) * g
            self.v[i] = self.betas[1] * self.v[i] + (1 - self.betas[1]) * (g**2)
            
            # Bias correction
            m_hat = self.m[i] / (1 - self.betas[0]**self.t)
            v_hat = self.v[i] / (1 - self.betas[1]**self.t)
            
            # Update weights
            p -= self.lr * m_hat / (np.sqrt(v_hat) + self.eps)
